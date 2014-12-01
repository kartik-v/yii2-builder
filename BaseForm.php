<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-builder
 * @version 1.4.0
 */

namespace kartik\builder;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Base form widget
 *
 * @property $form kartik\form\ActiveForm
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class BaseForm extends \yii\bootstrap\Widget
{
    // form inputs
    const INPUT_TEXT = 'textInput';
    const INPUT_TEXTAREA = 'textarea';
    const INPUT_PASSWORD = 'passwordInput';
    const INPUT_DROPDOWN_LIST = 'dropdownList';
    const INPUT_LIST_BOX = 'listBox';
    const INPUT_CHECKBOX = 'checkbox';
    const INPUT_RADIO = 'radio';
    const INPUT_CHECKBOX_LIST = 'checkboxList';
    const INPUT_RADIO_LIST = 'radioList';
    const INPUT_MULTISELECT = 'multiselect';
    const INPUT_STATIC = 'staticInput';
    const INPUT_FILE = 'fileInput';
    const INPUT_HTML5 = 'input';
    const INPUT_WIDGET = 'widget';
    const INPUT_RAW = 'raw'; // any free text or html markup

    /**
     * @var array the allowed valid list of input types
     */
    protected static $_validInputs = [
        self::INPUT_TEXT,
        self::INPUT_TEXTAREA,
        self::INPUT_PASSWORD,
        self::INPUT_DROPDOWN_LIST,
        self::INPUT_LIST_BOX,
        self::INPUT_CHECKBOX,
        self::INPUT_RADIO,
        self::INPUT_CHECKBOX_LIST,
        self::INPUT_RADIO_LIST,
        self::INPUT_MULTISELECT,
        self::INPUT_STATIC,
        self::INPUT_FILE,
        self::INPUT_HTML5,
        self::INPUT_WIDGET,
        self::INPUT_RAW
    ];

    /**
     * @var ActiveForm the form instance
     */
    public $form;

    /**
     * @var array the attribute settings. This is an associative array, which needs to be setup as
     * `$attribute_name => $attribute_settings`, where:
     * - `attribute_name`: string, the name of the attribute
     * - `attribute_settings`: array, the settings for the attribute, where you can set the following:
     *    - 'type': string, the input type for the attribute. Should be one of the INPUT_ constants.
     *       Defaults to `INPUT_TEXT`.
     *    - 'label': string, (optional) the custom attribute label. If this is not set, the model attribute label
     *      will be automatically used. If you set it to false, the `label` will be entirely hidden.
     *    - 'value': string|Closure, the value to be displayed if the `type` is set to `INPUT_RAW`. This will display
     *       the raw text from value field if it is a string. If this is a Closure, your anonymous function call should
     *       be of the type: `function ($model, $key, $index, $widget) { }, where $model is the current model, $key is
     *       the key associated with the data model $index is the zero based index of the dataProvider, and $widget
     *       is the current widget instance.`
     *    - 'fieldConfig': array, the configuration for the active field.
     *    - `hint`: string, the hint text to be shown below the active field.
     *    - 'items': array, the list of items if input type is one of the following:
     *      `INPUT_DROPDOWN_LIST`, `INPUT_LIST_BOX`, `INPUT_CHECKBOX_LIST`, `INPUT_RADIO_LIST`, `INPUT_MULTISELECT`
     *    - `enclosedByLabel`: bool, if the `INPUT_CHECKBOX` or `INPUT_RADIO` is to be enclosed by label. Defaults
     *      to `true`.
     *    - html5type: string, the type of HTML5 input, if input type is set to `INPUT_HTML5`.
     *    - 'widgetClass': string, the classname if input type is `INPUT_WIDGET`.
     *    - 'options': array, the HTML attributes or widget settings to be applied to the input.
     *    - 'columnOptions': array, for a `Form`, it will override columnOptions setup at `Form` level. For
     *      a `TabularForm` it will allow you to append additional column options for the grid data column.
     */
    public $attributes = [];

    /**
     * Initializes the widget
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->form) || !$this->form instanceof \kartik\form\ActiveForm) {
            throw new InvalidConfigException("The 'form' property must be set and must be an instance of '\\kartik\\widgets\\ActiveForm' or '\\kartik\\form\\ActiveForm'.");
        }
        if (empty($this->attributes)) {
            throw new InvalidConfigException("The 'attributes' array must be set.");
        }
    }

    /**
     * Renders each input based on the attribute settings
     *
     * @param \kartik\form\ActiveForm $form the form instance
     * @param \yii\db\ActiveRecord|\yii\base\Model $model 
     * @param string $attribute the name of the attribute
     * @param array $settings the attribute settings
     * @return \kartik\form\ActiveField
     * @throws \yii\base\InvalidConfigException
     *
     */
    protected static function renderInput($form, $model, $attribute, $settings)
    {
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        $i = strpos($attribute, ']');
        $attribName = $i > 0 ? substr($attribute, $i + 1) : $attribute;
        if (!in_array($type, static::$_validInputs)) {
            throw new InvalidConfigException("Invalid input type '{$type}' configured for the attribute '{$attribName}'.'");
        }
        $fieldConfig = ArrayHelper::getValue($settings, 'fieldConfig', []);
        $options = ArrayHelper::getValue($settings, 'options', []);
        $label = ArrayHelper::getValue($settings, 'label', null);
        $hint = ArrayHelper::getValue($settings, 'hint', null);
        $field = $form->field($model, $attribute, $fieldConfig);
        if ($type === self::INPUT_TEXT || $type === self::INPUT_PASSWORD || $type === self::INPUT_TEXTAREA ||
            $type === self::INPUT_FILE || $type === self::INPUT_STATIC
        ) {
            return static::getInput($field->$type($options), $label, $hint);
        }
        if ($type === self::INPUT_DROPDOWN_LIST || $type === self::INPUT_LIST_BOX || $type === self::INPUT_CHECKBOX_LIST ||
            $type === self::INPUT_RADIO_LIST || $type === self::INPUT_MULTISELECT
        ) {
            if (!isset($settings['items'])) {
                throw new InvalidConfigException("You must setup the 'items' array for attribute '{$attribName}' since it is a '{$type}'.");
            }
            return static::getInput($field->$type($settings['items'], $options), $label, $hint);
        }
        if ($type === self::INPUT_CHECKBOX || $type === self::INPUT_RADIO) {
            $enclosedByLabel = ArrayHelper::getValue($settings, 'enclosedByLabel', true);
            if ($label !== null) {
                $options['label'] = $label;
            }
            return static::getInput($field->$type($options, $enclosedByLabel), null, $hint);
        }
        if ($type === self::INPUT_HTML5) {
            $html5type = ArrayHelper::getValue($settings, 'html5type', 'text');
            return static::getInput($field->$type($html5type, $options), $label, $hint);
        }
        if ($type === self::INPUT_WIDGET) {
            $widgetClass = ArrayHelper::getValue($settings, 'widgetClass', []);
            if (empty($widgetClass) && !$widgetClass instanceof yii\widgets\InputWidget) {
                throw new InvalidConfigException("A valid 'widgetClass' for '{$attribute}' must be setup and extend from 'yii\\widgets\\InputWidget'.");
            }
            return static::getInput($field->$type($widgetClass, $options), $label, $hint);
        }
        if ($type === self::INPUT_RAW) {
            return ArrayHelper::getValue($settings, 'value', '');
        }
    }

    /*
     * Generates the active field input by parsing the label and hint
     * @param \kartik\widgets\ActiveField $field
     * @param string $label the label for the field
     * @param string $hint the hint for the field
     */
    protected static function getInput($field, $label = null, $hint = null)
    {
        if ($label !== null) {
            $field = $field->label($label);
        }
        if ($hint !== null) {
            $field = $field->hint($hint);
        }
        return $field;
    }
}