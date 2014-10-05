<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-builder
 * @version 1.0.0
 */

namespace kartik\builder;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * A form builder widget for rendering the form attributes using kartik\widgets\ActiveForm.
 * The widget uses Bootstrap 3.x styling for generating form styles and multiple field columns.
 *
 * Usage:
 * ```
 *   use kartik\widgets\ActiveForm;
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
 * @property $model yii\db\ActiveRecord|yii\base\Model
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
     * @var Model|ActiveRecord the model used for the form
     */
    public $model;

    /**
     * @var integer, the number of columns in which to split the fields horizontally. If not set, defaults to 1 column.
     */
    public $columns = 1;

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
     * @var array the HTML attributes for the field/attributes container. The following options are additionally recognized:
     * - `tag`: the HTML tag for the container. Defaults to `fieldset`.
     */
    public $options = [];

    /**
     * @var string the tag for the fieldset
     */
    private $_tag;

    /**
     * Initializes the widget
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->model) || !$this->model instanceof \yii\base\Model) {
            throw new InvalidConfigException("The 'model' property must be set and must extend from '\\yii\\base\\Model'.");
        }
        $this->initOptions();
        $this->registerAssets();
        echo Html::beginTag($this->_tag, $this->options) . "\n";
    }

    /**
     * Runs the widget
     *
     * @return string|void
     */
    public function run()
    {
        echo $this->renderFieldSet();
        echo Html::endTag($this->_tag);
        parent::run();
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
     * Renders the field set
     *
     * @return string
     */
    protected function renderFieldSet()
    {
        $content = '';
        $cols = (is_int($this->columns) && $this->columns >= 1) ? $this->columns : 1;
        if ($cols == 1) {
            $index = 0;
            foreach ($this->attributes as $attribute => $settings) {
                $content .= $this->parseInput($this->form, $this->model, $attribute, $settings, $index) . "\n";
                $index++;
            }
            return $content;
        }

        $index = 0;
        $attrCount = count($this->attributes);
        $rows = (float)($attrCount / $cols);
        $rows = ceil($rows);
        $names = array_keys($this->attributes);
        $values = array_values($this->attributes);
        $width = (int)(self::GRID_WIDTH / $cols);
        Html::addCssClass($this->rowOptions, 'row');

        for ($row = 1; $row <= $rows; $row++) {
            $content .= Html::beginTag('div', $this->rowOptions) . "\n";
            for ($col = 1; $col <= $cols; $col++) {
                if ($index > ($attrCount - 1)) {
                    break;
                }
                $attribute = $names[$index];
                $settings = $values[$index];
                $colOptions = ArrayHelper::getValue($settings, 'columnOptions', $this->columnOptions);
                $colWidth = $width;
                if (isset($colOptions['colspan'])) {
                    $colWidth = $colWidth * (int)($colOptions['colspan']);
                    unset($colOptions['colspan']);
                }
                $colWidth = (int)$colWidth;
                Html::addCssClass($colOptions, 'col-' . $this->columnSize . '-' . $colWidth);
                $content .= "\t" . Html::beginTag('div', $colOptions) . "\n";
                $content .= "\t\t" . $this->parseInput($this->form, $this->model, $attribute, $settings, $index) . "\n";
                $content .= "\t" . Html::endTag('div') . "\n";
                $index++;
            }
            $content .= Html::endTag('div') . "\n";
        }
        return $content;
    }

    /**
     * Parses input for `INPUT_RAW` type
     */
    protected function parseInput($form, $model, $attribute, $settings, $index)
    {
        $type = ArrayHelper::getValue($settings, 'type', self::INPUT_TEXT);
        if ($type === self::INPUT_RAW) {
            return ($settings['value'] instanceof \Closure) ? call_user_func($settings['value'], $model, $index, $this) : $settings['value'];
        } else {
            return static::renderInput($form, $model, $attribute, $settings);
        }
    }

    /**
     * Registers widget assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        FormAsset::register($view);
    }
}
