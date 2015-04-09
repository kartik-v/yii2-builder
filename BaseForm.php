<?php

/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @version   1.6.1
 */

namespace kartik\builder;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveField;

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
    use FormTrait;

    // form inputs
    const INPUT_STATIC = 'staticInput';
    const INPUT_HIDDEN = 'hiddenInput';
    const INPUT_HIDDEN_STATIC = 'hiddenStaticInput';
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
        self::INPUT_HIDDEN,
        self::INPUT_HIDDEN_STATIC,
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
     * @var string the form name to be provided if not using with model
     * and ActiveForm
     */
    public $formName;

    /**
     * @var array the attribute settings. This is an associative array, which needs to be setup as
     * `$attribute_name => $attribute_settings`, where:
     * - `attribute_name`: string, the name of the attribute
     * - `attribute_settings`: array, the settings for the attribute, where you can set the following:
     *    - 'type': string, the input type for the attribute. Should be one of the INPUT_ constants.
     *       Defaults to `INPUT_TEXT`.
     *    - 'attributes': array, the nested group of sub attributes that will be grouped together, this
     *      configuration will be similar to attributes. The label property will be auto set to `false`
     *      for each sub attribute.
     *    - 'value': string|Closure, the value to be displayed if the `type` is set to `INPUT_RAW` or `INPUT_STATIC`.
     *       This will display the raw text from value field if it is a string. If this is a Closure, your anonymous
     *       function call should be of the type: `function ($model, $key, $index, $widget) { }, where $model is the
     *       current model, $key is the key associated with the data model $index is the zero based index of the
     *       dataProvider, and $widget is the current widget instance.`
     *    - 'staticValue': string|Closure, the value to be displayed for INPUT_STATIC. If not set, the value will be
     *      automatically generated from the `value` setting above OR from the value of the model attribute. If this
     *      is a Closure, your anonymous function call should be of the type:
     *      `function ($model, $key, $index, $widget) { }, where $model is the current model, $key is the key
     *       associated with the data model $index is the zero based index of the dataProvider, and
     *       $widget is the current widget instance.`
     *    - 'format': string|array, applicable only for `INPUT_STATIC` type (and only in tabular forms). This
     *      controls which format should the value of each data model be displayed as (e.g. `"raw"`, `"text"`,
     *      `"html"`, `['date', 'php:Y-m-d']`). Supported formats are determined by [Yii::$app->formatter].
     *      Default format is "raw".
     *    - 'hiddenStaticOptions': array, HTML attributes for the static control container and applicable only
     *      for `INPUT_HIDDEN_STATIC` type.
     *    - 'label': string, (optional) the custom attribute label. If this is not set, the model attribute label
     *      will be automatically used. If you set it to false, the `label` will be entirely hidden.
     *    - 'labelSpan': int, the grid span width of the label container, which is especially useful for horizontal
     *     forms. If not set this will be derived automatically from the `formConfig['labelSpan']` property of `$form`
     *     (ActiveForm).
     *    - 'labelOptions': array, (optional) the HTML attributes for the label. Will be applied only when NOT using
     *      with active form and only if label is set.
     *    - 'prepend': string, (optional) any markup to prepend before the input. For ActiveForm fields, this content
     *      will be prepended before the field group (including label, input, error, hint blocks).
     *    - 'append': string, (optional) any markup to append before the input. For ActiveForm fields, this content
     *      will be appended after the field group (including label, input, error, hint blocks).
     *    - 'container': array, (optional) HTML attributes for the `div` container to wrap the input. For ActiveForm,
     *      this will envelop the field group (including label, input, error, hint blocks). If not set or empty, no
     *      container will be wrapped.
     *    - 'inputContainer': array, (optional) HTML attributes for the `div` container to wrap the
     *      input control only. If not set or empty, no container will be wrapped. Will be applied
     *      only when NOT using with ActiveForm.
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
     * @var array the default settings that will be applied for all attributes. The array will be
     * configured similar to a single attribute setting value in the `$attributes` array. One will typically
     * default markup and styling like `type`, `container`, `prepend`, `append` etc. The settings
     * at the `$attributes` level will override these default settings.
     */
    public $attributeDefaults = [];

    /**
     * @var bool whether all inputs in the form are to be static only
     */
    public $staticOnly = false;

    /**
     * Initializes the widget
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->checkBaseConfig();
    }

    /**
     * Renders active input based on the attribute settings.
     * This includes additional markup like rendering content before
     * and after input, and wrapping input in a container if set.
     *
     * @param \kartik\form\ActiveForm              $form the form instance
     * @param \yii\db\ActiveRecord|\yii\base\Model $model
     * @param string                               $attribute the name of the attribute
     * @param array                                $settings the attribute settings
     *
     * @return \kartik\form\ActiveField
     * @throws \yii\base\InvalidConfigException
     *
     */
    protected static function renderActiveInput($form, $model, $attribute, $settings)
    {
        $container = ArrayHelper::getValue($settings, 'container', []);
        $prepend = ArrayHelper::getValue($settings, 'prepend', '');
        $append = ArrayHelper::getValue($settings, 'append', '');
        $input = static::renderRawActiveInput($form, $model, $attribute, $settings);
        $out = $prepend . "\n" . $input . "\n" . $append;
        return empty($container) ? $out : Html::tag('div', $out, $container);
    }

    /**
     * Renders normal form input based on the attribute settings.
     * This includes additional markup like rendering content before
     * and after input, and wrapping input in a container if set.
     *
     * @param string $attribute the name of the attribute
     * @param array  $settings the attribute settings
     *
     * @return string the form input markup
     * @throws \yii\base\InvalidConfigException
     */
    protected static function renderInput($attribute, $settings = [])
    {
        $for = '';
        $input = static::renderRawInput($attribute, $settings, $for);
        $label = ArrayHelper::getValue($settings, 'label', false);
        $labelOptions = ArrayHelper::getValue($settings, 'labelOptions', []);
        Html::addCssClass($labelOptions, 'control-label');
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        $options = ArrayHelper::getValue($settings, 'options', []);
        $label = $label !== false && !empty($for) ? Html::label($label, $for, $labelOptions) . "\n" : '';
        $container = ArrayHelper::getValue($settings, 'container', []);
        $prepend = ArrayHelper::getValue($settings, 'prepend', '');
        $append = ArrayHelper::getValue($settings, 'append', '');
        $inputContainer = ArrayHelper::getValue($settings, 'inputContainer', []);
        if (!empty($inputContainer)) {
            $input = Html::tag('div', $input, $inputContainer);
        }
        $out = $prepend . "\n" . $label . $input . "\n" . $append;
        return empty($container) ? $out : Html::tag('div', $out, $container);
    }

    /**
     * Renders raw active input based on the attribute settings
     *
     * @param \kartik\form\ActiveForm              $form the form instance
     * @param \yii\db\ActiveRecord|\yii\base\Model $model
     * @param string                               $attribute the name of the attribute
     * @param array                                $settings the attribute settings
     *
     * @return \kartik\form\ActiveField
     * @throws \yii\base\InvalidConfigException
     *
     */
    protected static function renderRawActiveInput($form, $model, $attribute, $settings)
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
        if ($type === self::INPUT_TEXT || $type === self::INPUT_PASSWORD ||
            $type === self::INPUT_TEXTAREA || $type === self::INPUT_FILE ||
            $type === self::INPUT_HIDDEN || $type === self::INPUT_STATIC
        ) {
            return static::getInput($field->$type($options), $label, $hint);
        } elseif ($type === self::INPUT_HIDDEN_STATIC) {
            $staticOptions = ArrayHelper::getValue($settings, 'hiddenStaticOptions', []);
            return static::getInput($field->staticInput($staticOptions), $label, $hint) .
            static::getInput($field->hiddenInput($options));
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

    /**
     * Renders raw form input based on the attribute settings
     *
     * @param string $attribute the name of the attribute
     * @param array  $settings the attribute settings
     *
     * @return string the form input markup
     * @throws \yii\base\InvalidConfigException
     */
    protected static function renderRawInput($attribute, $settings = [], &$id)
    {
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        $i = strpos($attribute, ']');
        $attribName = $i > 0 ? substr($attribute, $i + 1) : $attribute;
        if (!in_array($type, static::$_validInputs)) {
            throw new InvalidConfigException("Invalid input type '{$type}' configured for the attribute '{$attribName}'.'");
        }
        $value = ArrayHelper::getValue($settings, 'value', null);
        $options = ArrayHelper::getValue($settings, 'options', []);
        $id = str_replace(['[]', '][', '[', ']', ' '], ['', '-', '-', '', '-'], $attribute);
        $id = strtolower($id);
        if ($type === self::INPUT_WIDGET) {
            $id = empty($options['options']['id']) ? $id : $options['options']['id'];
            $options['options']['id'] = $id;
        } else {
            $id = empty($options['id']) ? $id : $options['id'];
            $options['id'] = $id;
        }
        if ($type === self::INPUT_STATIC || $type === self::INPUT_HIDDEN_STATIC) {
            $opts = $options;
            if ($type === self::INPUT_HIDDEN_STATIC) {
                $opts = ArrayHelper::getValue($settings, 'hiddenStaticOptions', []);
            }
            Html::addCssClass($opts, 'form-control-static');
            $out = Html::tag('p', $value, $opts);
            if ($type === self::INPUT_HIDDEN_STATIC) {
                return $out . Html::hiddenInput($attribute, $value, $opts);
            }
            return $out;
        }
        Html::addCssClass($options, 'form-control');
        if ($type === self::INPUT_TEXT || $type === self::INPUT_PASSWORD || $type === self::INPUT_TEXTAREA ||
            $type === self::INPUT_FILE || $type === self::INPUT_HIDDEN
        ) {
            return Html::$type($attribute, $value, $options);
        }

        if ($type === self::INPUT_DROPDOWN_LIST || $type === self::INPUT_LIST_BOX || $type === self::INPUT_CHECKBOX_LIST ||
            $type === self::INPUT_RADIO_LIST || $type === self::INPUT_MULTISELECT
        ) {
            if (!isset($settings['items'])) {
                throw new InvalidConfigException("You must setup the 'items' array for attribute '{$attribName}' since it is a '{$type}'.");
            }
            $items = ArrayHelper::getValue($settings, 'items', []);
            return Html::$type($attribute, $value, $items, $options);
        }
        if ($type === self::INPUT_CHECKBOX || $type === self::INPUT_RADIO) {
            $enclosedByLabel = ArrayHelper::getValue($settings, 'enclosedByLabel', true);
            $checked = !empty($value) && ($value !== false) ? true : false;
            $out = Html::$type($attribute, $checked, $options);
            return $enclosedByLabel ? "<div class='{$type}'>{$out}</div>" : $out;
        }
        if ($type === self::INPUT_HTML5) {
            $html5type = ArrayHelper::getValue($settings, 'html5type', 'text');
            return Html::input($type, $attribute, $value, $options);
        }
        if ($type === self::INPUT_WIDGET) {
            $widgetClass = ArrayHelper::getValue($settings, 'widgetClass', []);
            if (empty($widgetClass) && !$widgetClass instanceof yii\widgets\InputWidget) {
                throw new InvalidConfigException("A valid 'widgetClass' for '{$attribute}' must be setup and extend from 'yii\\widgets\\InputWidget'.");
            }
            $options['name'] = $attribute;
            $options['value'] = $value;
            return $widgetClass::widget($options);
        }
        if ($type === self::INPUT_RAW) {
            $value = ArrayHelper::getValue($settings, 'value', '');
        }
    }

    /**
     * Generates the active field input by parsing the label and hint
     *
     * @param ActiveField $field
     * @param string      $label the label for the field
     * @param string      $hint the hint for the field
     *
     * @return ActiveField
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