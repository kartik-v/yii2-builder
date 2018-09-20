<?php
/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2018
 * @version   1.6.4
 */

namespace kartik\builder;

use Closure;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use kartik\helpers\Html;
use kartik\form\ActiveForm;

/**
 * A form builder widget for rendering the form attributes using [[ActiveForm]]. The widget uses Bootstrap 3.x styling
 * for generating form styles and multiple field columns.
 *
 * Usage example:
 *
 * ~~~
 * use kartik\form\ActiveForm;
 * use kartik\builder\Form;
 * $form = ActiveForm::begin($options); // $options will be your form config array params.
 * echo Form::widget([
 *     'model' => $model, // your model
 *     'form' => $form,
 *     'columns' => 2,
 *     'attributes' => [
 *         'username' => ['type' => Form::INPUT_TEXT, 'options'=> ['placeholder'=>'Enter username...']],
 *         'password' => ['type' => Form::INPUT_PASSWORD],
 *         'rememberMe' => ['type' => Form::INPUT_CHECKBOX, 'enclosedByLabel' => true],
 *     ]
 * ]);
 * ActiveForm::end();
 * ~~~
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class Form extends BaseForm
{
    /**
     * Large size for the grid column (bootstrap styled).
     */
    const SIZE_LARGE = 'lg';
    /**
     * Medium size for the grid column (bootstrap styled).
     */
    const SIZE_MEDIUM = 'md';
    /**
     * Small size for the grid column (bootstrap styled).
     */
    const SIZE_SMALL = 'sm';
    /**
     * Extra small (tiny) size for the grid column (bootstrap styled).
     */
    const SIZE_TINY = 'xs';
    /**
     * Maximum bootstrap grid width (layout columns count).
     */
    const GRID_WIDTH = 12;
    /**
     * [[ActiveFormEvent]] triggered before parsing input.
     */
    const EVENT_BEFORE_PARSE_INPUT = 'eBeforeParseInput';
    /**
     * [[ActiveFormEvent]] triggered after parsing input.
     */
    const EVENT_AFTER_PARSE_INPUT = 'eAfterParseInput';
    /**
     * [[ActiveFormEvent]] triggered before rendering sub attribute.
     */
    const EVENT_BEFORE_RENDER_SUB_ATTR = 'eBeforeRenderSubAttr';
    /**
     * [[ActiveFormEvent]] triggered after rendering sub attribute.
     */
    const EVENT_AFTER_RENDER_SUB_ATTR = 'eAfterRenderSubAttr';

    /**
     * @var Model the data model used for the form.
     */
    public $model;

    /**
     * @var string content to display before the generated form fields. This is not HTML encoded.
     */
    public $contentBefore = '';

    /**
     * @var string content to display after the generated form fields. This is not HTML encoded.
     */
    public $contentAfter = '';

    /**
     * @var integer the number of columns in which to split the fields horizontally. If not set, defaults to 1 column.
     */
    public $columns = 1;

    /**
     * @var bool whether to use a compact row/column grid layout as supported by Bootstrap 4.x via `form-row` class.
     * Supported only when [[bsVersion]] = '4.x'
     * @see https://getbootstrap.com/docs/4.0/components/forms/#form-row
     */
    public $compactGrid = false;

    /**
     * @var boolean calculate the number of columns automatically based on count of attributes configured in the
     * [[Form]] widget. Columns will be created maximum upto the [[GRID_WIDTH]].
     */
    public $autoGenerateColumns = false;

    /**
     * @var string the bootstrap device size for rendering each grid column. Defaults to [[SIZE_SMALL]].
     */
    public $columnSize = self::SIZE_SMALL;

    /**
     * @var array the HTML attributes for the grid columns. Applicable only if [[columns]] is greater than `1`.
     */
    public $columnOptions = [];

    /**
     * @var array the HTML attributes for the rows. Applicable only if [[columns]] is greater than `1`.
     */
    public $rowOptions = [];

    /**
     * @var array the HTML attributes for the field/attributes container. The following options are additionally
     * recognized:
     * - `tag`: _string_, the HTML tag for the container. Defaults to `fieldset`.
     */
    public $options = [];

    /**
     * @var bool whether to enclose and render the fieldset container
     */
    public $encloseFieldSet = false;

    /**
     * @var string the tag for the fieldset.
     */
    private $_fieldsetTag;

    /**
     * @var string the form orientation.
     */
    private $_orientation = ActiveForm::TYPE_VERTICAL;

    /**
     * @var string the Bootstrap grid row CSS
     */
    private $_rowCss;

    /**
     * @var string the Bootstrap Label CSS
     */
    private $_labelCss;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->initOptions();
        $this->registerAssets();
        if ($this->autoGenerateColumns) {
            $cols = count($this->attributes);
            $this->columns = $cols >= self::GRID_WIDTH ? self::GRID_WIDTH : $cols;
        }
        if ($this->encloseFieldSet) {
            echo Html::beginTag($this->_fieldsetTag, $this->options) . "\n";
        }
    }

    /**
     * Initializes the widget options
     */
    protected function initOptions()
    {
        $this->checkFormConfig();
        if (empty($this->columnSize)) {
            $this->columnSize = empty($this->form->formConfig['deviceSize']) ?
                self::SIZE_SMALL :
                $this->form->formConfig['deviceSize'];
        }
        if (isset($this->form->type)) {
            $this->_orientation = $this->form->type;
        }
        if (!isset($this->form->bsVersion) && isset($this->bsVersion)) {
            $this->form->bsVersion = $this->bsVersion;
        }
        $this->_fieldsetTag = ArrayHelper::remove($this->options, 'tag', 'fieldset');
        $isBs4 = $this->isBs4();
        $this->_labelCss = $isBs4 ? 'col-form-label' : 'control-label';
        $this->_rowCss = $isBs4 && $this->compactGrid ? 'form-row' : 'row';
    }

    /**
     * Registers widget assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        FormAsset::register($view);
        $view->registerJs('jQuery("#' . $this->options['id'] . '").kvFormBuilder({});');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo $this->contentBefore;
        echo $this->renderFieldSet();
        echo $this->contentAfter;
        if ($this->encloseFieldSet) {
            echo Html::endTag($this->_fieldsetTag);
        }
        parent::run();
    }

    /**
     * Renders the field set.
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function renderFieldSet()
    {
        $content = '';
        $cols = (is_int($this->columns) && $this->columns >= 1) ? $this->columns : 1;
        $index = 0;
        $attrCount = count($this->attributes);
        $rows = (float)($attrCount / $cols);
        $rows = ceil($rows);
        $names = array_keys($this->attributes);
        $values = array_values($this->attributes);
        $width = (int)(self::GRID_WIDTH / $cols);
        Html::addCssClass($this->rowOptions, $this->_rowCss);
        $skip = ($attrCount == 1);
        for ($row = 1; $row <= $rows; $row++) {
            $content .= $this->beginTag('div', $this->rowOptions, $skip);
            for ($col = 1; $col <= $cols; $col++) {
                if ($index > ($attrCount - 1)) {
                    break;
                }
                $attribute = $names[$index];
                $settings = $values[$index];
                $settings = array_replace_recursive($this->attributeDefaults, $settings);
                $colOptions = ArrayHelper::getValue($settings, 'columnOptions', $this->columnOptions);
                $colWidth = $width;
                if (isset($colOptions['colspan'])) {
                    $colWidth = $colWidth * (int)($colOptions['colspan']);
                    unset($colOptions['colspan']);
                }
                $colWidth = (int)$colWidth;
                Html::addCssClass($colOptions, 'col-' . $this->columnSize . '-' . $colWidth);
                $content .= "\t" . $this->beginTag('div', $colOptions, $skip) . "\n";
                if (!empty($settings['attributes'])) {
                    $this->raise(self::EVENT_BEFORE_RENDER_SUB_ATTR, $attribute, $index, ['settings' => &$settings]);
                    $content .= $this->renderSubAttributes($settings, $index);
                    $this->raise(self::EVENT_AFTER_RENDER_SUB_ATTR, $attribute, $index, ['content' => &$content]);
                } else {
                    $this->raise(self::EVENT_BEFORE_PARSE_INPUT, $attribute, $index, ['settings' => &$settings]);
                    $content .= "\t\t" . $this->parseInput($attribute, $settings, $index) . "\n";
                    $this->raise(self::EVENT_AFTER_PARSE_INPUT, $attribute, $index, ['content' => &$content]);
                }
                $content .= "\t" . $this->endTag('div', $skip) . "\n";
                $index++;
            }
            $content .= $this->endTag('div', $skip) . "\n";
        }
        return $content;
    }

    /**
     * Render sub attributes.
     *
     * @param array  $settings the attribute settings
     * @param string $index the zero-based index of the attribute
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function renderSubAttributes($settings, $index)
    {
        $content = $this->getSubAttributesContent($settings, $index);
        $labelOptions = ArrayHelper::getValue($settings, 'labelOptions', []);
        $label = ArrayHelper::getValue($settings, 'label', '');
        if ($this->_orientation === ActiveForm::TYPE_INLINE) {
            Html::addCssClass($labelOptions, ActiveForm::SCREEN_READER);
        } elseif ($this->_orientation === ActiveForm::TYPE_VERTICAL) {
            Html::addCssClass($labelOptions, $this->_labelCss);
        }
        if ($this->_orientation !== ActiveForm::TYPE_HORIZONTAL) {
            return '<div class="kv-nested-attribute-block">' . "\n" .
            Html::label($label, null, $labelOptions) . "\n" .
            $content . "\n" .
            '</div>';
        }
        $defaultLabelSpan = ArrayHelper::getValue($this->form->formConfig, 'labelSpan', 3);
        $labelSpan = ArrayHelper::getValue($settings, 'labelSpan', $defaultLabelSpan);
        Html::addCssClass($labelOptions, ["col-{$this->columnSize}-{$labelSpan}", $this->_labelCss]);
        $inputSpan = self::GRID_WIDTH - $labelSpan;
        $rowCss = $this->isBs4() ? ' ' . $this->_rowCss : '';
        $rowOptions = ['class' => 'kv-nested-attribute-block form-sub-attributes form-group' . $rowCss];
        $inputOptions = ['class' => "col-{$this->columnSize}-{$inputSpan}"];
        return Html::beginTag('div', $rowOptions) . "\n" .
        Html::beginTag('label', $labelOptions) . "\n" .
        $label . "\n" .
        Html::endTag('label') . "\n" .
        Html::beginTag('div', $inputOptions) . "\n" .
        $content . "\n" .
        Html::endTag('div') . "\n" .
        Html::endTag('div') . "\n";
    }

    /**
     * Gets sub attribute markup content.
     *
     * @param array  $settings the attribute settings
     * @param string $index the zero-based index of the attribute
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function getSubAttributesContent($settings, $index)
    {
        $subIndex = 0;
        $defaultSubColOptions = ArrayHelper::getValue($settings, 'subColumnOptions', $this->columnOptions);
        $content = '';
        $content .= "\t" . $this->beginTag('div', $this->rowOptions) . "\n";
        $attrCount = count($settings['attributes']);
        $cols = ArrayHelper::getValue($settings, 'columns', $attrCount);
        foreach ($settings['attributes'] as $subAttr => $subSettings) {
            $subColWidth = (int)(self::GRID_WIDTH / $cols);
            $subSettings = array_replace_recursive($this->attributeDefaults, $subSettings);
            if (!isset($subSettings['label'])) {
                $subSettings['label'] = false;
            }
            $subColOptions = ArrayHelper::getValue($subSettings, 'columnOptions', $defaultSubColOptions);
            if (isset($subColOptions['colspan'])) {
                $subColWidth = (int)$subColWidth * (int)($subColOptions['colspan']);
                unset($subColOptions['colspan']);
            }
            Html::addCssClass($subColOptions, 'col-' . $this->columnSize . '-' . $subColWidth);
            $subSettings['columnOptions'] = $subColOptions;
            $subSettings['fieldConfig']['skipFormLayout'] = true;
            $content .= "\t\t" . $this->beginTag('div', $subColOptions) . "\n";
            /** @var integer $index */
            $content .= "\t\t\t" . $this->parseInput($subAttr, $subSettings, $index * 10 + $subIndex) . "\n";
            $subIndex++;
            $content .= "\t\t" . $this->endTag('div') . "\n";
        }
        $content .= "\t" . $this->endTag('div') . "\n";
        return $content;
    }

    /**
     * Parses the input markup based on type.
     *
     * @param string $attribute the model attribute.
     * @param array $settings the column settings.
     * @param integer $index the row index.
     *
     * @return string the generated input.
     * @throws InvalidConfigException
     */
    protected function parseInput($attribute, $settings, $index)
    {
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        if ($this->staticOnly === true) {
            if (isset($this->form)) {
                $this->form->staticOnly = true;
            } else {
                $settings['type'] = self::INPUT_STATIC;
            }
            if ($type !== self::INPUT_HIDDEN_STATIC) {
                $type = self::INPUT_STATIC;
            }
        }
        if (($type === self::INPUT_STATIC || $type === self::INPUT_HIDDEN_STATIC) && isset($settings['staticValue'])) {
            $val = $settings['staticValue'];
            if ($val instanceof Closure) {
                $val = call_user_func($val, $this->hasModel() ? $this->model : $this->formName, $index, $this);
            }
            if ($this->hasModel()) {
                $settings['fieldConfig']['staticValue'] = $val;
            } else {
                $settings['value'] = $val;
            }
        }
        $val = ArrayHelper::getValue($settings, 'value', null);
        if ($type === self::INPUT_RAW) {
            $source = $this->hasModel() ? $this->model : $this->formName;
            return $val instanceof Closure ? call_user_func($val, $source, $index, $this) : $val;
        } else {
            $hidden = '';
            if ($type === self::INPUT_HIDDEN || $type === self::INPUT_HIDDEN_STATIC) {
                $options = ArrayHelper::getValue($settings, 'options', []);
                $hidden = $this->hasModel() ? Html::activeHiddenInput($this->model, $attribute, $options) :
                    Html::hiddenInput("{$this->formName}[{$attribute}]", $val, $options);
                if ($type === self::INPUT_HIDDEN) {
                    return $hidden;
                }
                $settings['type'] = self::INPUT_STATIC;
                $settings['options'] = ArrayHelper::getValue($settings, 'hiddenStaticOptions', []);
            }
            if($this->hasModel()){
                $out=static::renderActiveInput($this->form, $this->model, $attribute, $settings);
            } else {
                //Check whether atrtribute defined in array way ([])
                if(substr($attribute,0,1)=='[' && substr($attribute,-1)==']') {
                    $out=static::renderInput("{$this->formName}{$attribute}", $settings);
                } else {
                    $out=static::renderInput("{$this->formName}[{$attribute}]", $settings);
                }
            }
            return $out . $hidden;
        }
    }

    /**
     * Begins a tag markup based on orientation.
     *
     * @param string $tag the HTML tag
     * @param array $options the HTML attributes for the tag
     * @param boolean $skip whether to skip the tag generation
     *
     * @return string
     */
    protected function beginTag($tag, $options = [], $skip = false)
    {
        if ($this->_orientation !== ActiveForm::TYPE_INLINE && !$skip) {
            return Html::beginTag($tag, $options) . "\n";
        }
        return '';
    }

    /**
     * Ends a tag markup based on orientation.
     *
     * @param string $tag the HTML tag
     * @param boolean $skip whether to skip the tag generation
     *
     * @return string
     */
    protected function endTag($tag, $skip = false)
    {
        if ($this->_orientation !== ActiveForm::TYPE_INLINE && !$skip) {
            return Html::endTag($tag) . "\n";
        }
        return '';
    }

    /**
     * Triggers an [[ActiveFormEvent]].
     *
     * @param string $event the event name.
     * @param string $attribute the attribute name.
     * @param integer|string $index the row index.
     * @param array $data the event data to pass to the event.
     */
    protected function raise($event = '', $attribute = '', $index = '', $data = [])
    {
        $this->trigger(
            $event,
            new ActiveFormEvent(['attribute' => $attribute, 'index' => $index, 'eventData' => $data])
        );
    }
}
