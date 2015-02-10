<?php
/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version   1.6.0
 */

namespace kartik\builder;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/**
 * A form builder widget for rendering the form attributes using kartik\form\ActiveForm.
 * The widget uses Bootstrap 3.x styling for generating form styles and multiple field columns.
 *
 * Usage:
 * ```
 *   use kartik\form\ActiveForm;
 *   use kartik\builder\Form;
 *   $form = ActiveForm::begin($options); // $options is array for your form config
 *   echo Form::widget([
 *       'model' => $model, // your model
 *       'form' => $form,
 *       'columns' => 2,
 *       'attributes' => [
 *           'username' => ['type' => Form::INPUT_TEXT, 'options'=> ['placeholder'=>'Enter username...']],
 *           'password' => ['type' => Form::INPUT_PASSWORD],
 *           'rememberMe' => ['type' => Form::INPUT_CHECKBOX, 'enclosedByLabel' => true],
 *       ]
 *   ]);
 *   ActiveForm::end();
 * ```
 *
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Form extends BaseForm
{
    // bootstrap grid column sizes
    const SIZE_LARGE = 'lg';
    const SIZE_MEDIUM = 'md';
    const SIZE_SMALL = 'sm';
    const SIZE_TINY = 'xs';

    // bootstrap maximum grid width
    const GRID_WIDTH = 12;

    /**
     * @var yii\db\ActiveRecord | yii\base\Model the model used for the form
     */
    public $model;

    /**
     * @var integer, the number of columns in which to split the fields horizontally. If not set, defaults to 1 column.
     */
    public $columns = 1;

    /**
     * @var boolean, calculate the number of columns automatically based on count of attributes
     * configured in the Form widget. Columns will be created max upto the Form::GRID_WIDTH.
     */
    public $autoGenerateColumns = false;

    /**
     * @var string, the bootstrap device size for rendering each grid column. Defaults to `SIZE_SMALL`.
     */
    public $columnSize = self::SIZE_SMALL;

    /**
     * @var array the HTML attributes for the grid columns. Applicable only if `$columns` is greater than 1.
     */
    public $columnOptions = [];

    /**
     * @var array the HTML attributes for the rows. Applicable only if `$columns` is greater than 1.
     */
    public $rowOptions = [];

    /**
     * @var array the HTML attributes for the field/attributes container. The following options are additionally
     *     recognized:
     * - `tag`: the HTML tag for the container. Defaults to `fieldset`.
     */
    public $options = [];

    /**
     * @var string the tag for the fieldset
     */
    private $_tag;

    /**
     * @var string the form orientation
     */
    private $_orientation = ActiveForm::TYPE_VERTICAL;

    /**
     * Initializes the widget
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->checkFormConfig();
        if (empty($this->columnSize)) {
            $this->columnSize = empty($this->form->formConfig['deviceSize']) ? 
                self::SIZE_SMALL : 
                $this->form->formConfig['deviceSize'];
        }        
        if (isset($this->form->type)) {
            $this->_orientation = $this->form->type;
        }
        $this->initOptions();
        $this->registerAssets();
        if ($this->autoGenerateColumns) {
            $cols = count($this->attributes);
            $this->columns = $cols >= self::GRID_WIDTH ? self::GRID_WIDTH : $cols;
        }
        echo Html::beginTag($this->_tag, $this->options) . "\n";
    }

    /**
     * Initializes the widget options
     */
    protected function initOptions()
    {
        $this->_tag = ArrayHelper::remove($this->options, 'tag', 'fieldset');
        if (empty($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
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
        echo $this->renderFieldSet();
        echo Html::endTag($this->_tag);
        parent::run();
    }

    /**
     * Renders the field set
     *
     * @return string
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
        Html::addCssClass($this->rowOptions, 'row');
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
                elseif (isset($colOptions['colWidth'])) {
                    $colWidth = $colOptions['colWidth'];
                    unset($colOptions['colWidth']);
                }
                $colWidth = (int)$colWidth;
                Html::addCssClass($colOptions, 'col-' . $this->columnSize . '-' . $colWidth);
                $content .= "\t" . $this->beginTag('div', $colOptions, $skip) . "\n";
                if (!empty($settings['attributes'])) {
                    $content .= $this->renderSubAttributes($attribute, $settings, $index);
                } else {
                    $content .= "\t\t" . $this->parseInput($attribute, $settings, $index) . "\n";
                }
                $content .= "\t" . $this->endTag('div', $skip) . "\n";
                $index++;
            }
            $content .= $this->endTag('div', $skip) . "\n";
        }
        return $content;
    }

    /**
     * Render sub attributes
     *
     * @return string
     */
    protected function renderSubAttributes($attribute, $settings, $index)
    {
        $content = $this->getSubAttributesContent($attribute, $settings, $index);
        $labelOptions = ArrayHelper::getValue($settings, 'labelOptions', []);
        $label = ArrayHelper::getValue($settings, 'label', '');
        if ($this->_orientation === ActiveForm::TYPE_INLINE) {
            Html::addCssClass($labelOptions, ActiveForm::SCREEN_READER);
        } elseif ($this->_orientation === ActiveForm::TYPE_VERTICAL) {
            Html::addCssClass($labelOptions, "control-label");
        }
        if ($this->_orientation !== ActiveForm::TYPE_HORIZONTAL) {
            return '<div class="kv-nested-attribute-block">' . "\n" .
                Html::label($label, null, $labelOptions) . "\n" .
                $content . "\n" .
            '</div>';
        }
        if (isset($this->form->formConfig['labelSpan'])) {
            $defaultLabelSpan = $this->form->formConfig['labelSpan'];
        }
        $labelSpan = ArrayHelper::getValue($settings, 'labelSpan', 3);
        Html::addCssClass($labelOptions, "col-{$this->columnSize}-{$labelSpan} control-label");
        $inputSpan = self::GRID_WIDTH - $labelSpan;
        $rowOptions = ['class' => 'kv-nested-attribute-block form-sub-attributes form-group'];
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
     * Gets sub attribute markup content
     *
     * @return string
     */
    protected function getSubAttributesContent($attribute, $settings, $index)
    {
        $subIndex = 0;
        $defaultSubColOptions = ArrayHelper::getValue($settings, 'subColumnOptions', $this->columnOptions);
        $labelOptions = ArrayHelper::getValue($settings, 'labelOptions', []);
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
            $content .= "\t\t" . $this->beginTag('div', $subColOptions) . "\n";
            $content .= "\t\t\t" . $this->parseInput($subAttr, $subSettings, $index * 10 + $subIndex) . "\n";
            $subIndex++;
            $content .= "\t\t" . $this->endTag('div') . "\n";
        }
        $content .= "\t" . $this->endTag('div') . "\n";
        return $content;
    }

    /**
     * Parses input for `INPUT_RAW` type
     *
     * @param string $attribute the model attribute
     * @param string $settings the column settings
     * @param int    $index the row index
     *
     * @return \kartik\form\ActiveField|mixed
     * @throws InvalidConfigException
     */
    protected function parseInput($attribute, $settings, $index)
    {
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        if ($type === self::INPUT_RAW) {
            if ($this->hasModel()) {
                return ($settings['value'] instanceof \Closure) ?
                    call_user_func($settings['value'], $this->model, $index, $this) :
                    $settings['value'];
            } else {
                return ($settings['value'] instanceof \Closure) ?
                    call_user_func($settings['value'], $this->formName, $index, $this) :
                    $settings['value'];
            }
        } else {
            return $this->hasModel() ?
                static::renderActiveInput($this->form, $this->model, $attribute, $settings) :
                static::renderInput("{$this->formName}[{$attribute}]", $settings);
        }
    }

    /**
     * Begins a tag markup based on orientation
     *
     * @return string
     */
    protected function beginTag($tag, $options, $skip = false)
    {
        if ($this->_orientation !== ActiveForm::TYPE_INLINE && !$skip) {
            return Html::beginTag($tag, $options) . "\n";
        }
        return '';
    }

    /**
     * Ends a tag markup based on orientation
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
}