<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-widgets
 * @version 1.0.0
 */

namespace kartik\builder;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Base form widget
 *
 * @property $form kartik\widgets\ActiveForm
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
     *      will be automatically used. If you set it to an empty string or null, it will not be displayed.
     *    - 'value': string|Closure, the value to be displayed if the `type` is set to `INPUT_RAW`. This will display
     *       the raw text from value field if it is a string. If this is a Closure, your anonymous function call should
     *       be of the type: `function ($model, $index, $widget) { }, where $model is the current model, $index is the
     *       zero based index of the dataProvider, and $widget is the current widget instance.` The Closure option is only
     *       applicable for the Tabular Form widget.
     *    - 'fieldConfig': array, the configuration for the active field.
     *    - 'widgetClass': string, the classname if input type is `INPUT_WIDGET`.
     *    - 'items': array, the list of items if input type is one of the following:
     *      `INPUT_DROPDOWN_LIST`, `INPUT_LIST_BOX`, `INPUT_CHECKBOX_LIST`, `INPUT_RADIO_LIST`, `INPUT_MULTISELECT`
     *    - `enclosedByLabel`: bool, if the `INPUT_CHECKBOX` or `INPUT_RADIO` is to be enclosed by label. Defaults
     *      to `true`.
     *    - html5type: string, the type of HTML5 input, if input type is set to `INPUT_HTML5`.
     *    - 'options': array, the HTML attributes or widget settings to be applied to the input.
     *    - 'columnOptions': array, additional options for the grid data column (applicable only for TabularForm)
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
        if (empty($this->form) || !$this->form instanceof \kartik\widgets\ActiveForm) {
            throw new InvalidConfigException("The 'form' property must be set and must be an instance of '\\kartik\\widgets\\ActiveForm'.");
        }
        if (empty($this->attributes)) {
            throw new InvalidConfigException("The 'attributes' array must be set.");
        }
    }

    /**
     * Renders each input based on the attribute settings
     *
     * @param $form \kartik\widgets\ActiveForm the form instance
     * @param $model \yii\db\ActiveRecord|\yii\base\Model
     * @param $attribute string the name of the attribute
     * @param $settings array the attribute settings
     * @return \kartik\widgets\ActiveField
     * @throws \yii\base\InvalidConfigException
     *
     */
    protected static function renderInput($form, $model, $attribute, $settings)
    {
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        $fieldConfig = ArrayHelper::getValue($settings, 'fieldConfig', []);
        if (isset($settings['label'])) {
            $template = ArrayHelper::getValue($fieldConfig, 'template', "{label}\n{input}\n{hint}\n{error}");
            $fieldConfig['template'] = strtr($template, ["{label}\n" => $settings['label'], "{label}" => $settings['label']]);
        }

        $options = ArrayHelper::getValue($settings, 'options', []);
        if ($type === self::INPUT_TEXT || $type === self::INPUT_PASSWORD || $type === self::INPUT_FILE || $type === self::INPUT_STATIC) {
            return $form->field($model, $attribute, $fieldConfig)->$type($options);
        }
        if ($type === self::INPUT_DROPDOWN_LIST || $type === self::INPUT_LIST_BOX || $type === self::INPUT_CHECKBOX_LIST ||
            $type === self::INPUT_RADIO_LIST || $type === self::INPUT_MULTISELECT
        ) {
            $items = ArrayHelper::getValue($settings, 'items', []);
            if (empty($items)) {
                throw new InvalidConfigException("You must setup the 'items' array for '{$attribute}' since it is a '{$type}'.");
            }
            return $form->field($model, $attribute, $fieldConfig)->$type($items, $options);
        }
        if ($type === self::INPUT_CHECKBOX || $type === self::INPUT_RADIO) {
            $enclosedByLabel = ArrayHelper::getValue($settings, 'enclosedByLabel', true);
            return $form->field($model, $attribute, $fieldConfig)->$type($options, $enclosedByLabel);
        }
        if ($type === self::INPUT_HTML5) {
            $html5type = ArrayHelper::getValue($settings, 'html5type', 'text');
            return $form->field($model, $attribute, $fieldConfig)->$type($html5type, $options);
        }
        if ($type === self::INPUT_WIDGET) {
            $widgetClass = ArrayHelper::getValue($settings, 'widgetClass', []);
            if (empty($widgetClass) && !$widgetClass instanceof yii\widgets\InputWidget) {
                throw new InvalidConfigException("A valid 'widgetClass' for '{$attribute}' must be setup and extend from 'yii\\widgets\\InputWidget'.");
            }
            return $form->field($model, $attribute, $fieldConfig)->$type($widgetClass, $options);
        }
        if ($type === self::INPUT_RAW) {
            return ArrayHelper::getValue($settings, 'value', '');
        }
    }
}