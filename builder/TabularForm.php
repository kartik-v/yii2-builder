<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-widgets
 * @version 1.0.0
 */

namespace kartik\builder;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\widgets\ActiveForm;

/**
 * A tabular form builder widget using kartik\widgets\ActiveForm.
 *
 * Usage:
 * ```
 *   use kartik\widgets\ActiveForm;
 *   use kartik\builder\TabularForm;
 *   $form = ActiveForm::begin($options); // $options is array for your form config
 *   echo TabularForm::widget([
 *       'model' => $model, // your model
 *       'form' => $form,
 *       'gridSettings' => [
 *           'toolbar' => \yii\helpers\Html::submitButton('Submit')
 *       ],
 *       'attributes' => [
 *           'id' => ['type' => TabularForm::INPUT_STATIC],
 *           'name' => ['type' => TabularForm::INPUT_TEXT],
 *           'description' => ['type' => TabularForm::INPUT_TEXT],
 *           'status' => ['type' => TabularForm::INPUT_CHECKBOX, 'enclosedByLabel' => true],
 *       ]
 *   ]);
 *   ActiveForm::end();
 * ```
 *
 * @property $model yii\db\ActiveRecord|yii\base\Model
 * @property $form kartik\widgets\ActiveForm
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class TabularForm extends BaseForm
{
    /**
     * @var \yii\data\ActiveDataProvider the data provider for the tabular form. This property is required.
     * It must return an instance of ActiveDataProvider and return a list of models.
     */
    public $dataProvider;

    /**
     * @var string the class when a row is selected
     */
    public $rowSelectedClass = GridView::TYPE_INFO;

    /**
     * @var array the settings for `\kartik\widgets\GridView` widget which will display the tabular form content.
     */
    public $gridSettings = [];

    /**
     * @var array|boolean the settings for the serial column.
     * If set to false will not be displayed.
     */
    public $serialColumn = [];

    /**
     * @var array|boolean the settings for the checkbox column.
     * If set to false will not be displayed.
     */
    public $checkboxColumn = [];

    /**
     * @var array the settings for the action column.
     * If set to false will not be displayed.
     */
    public $actionColumn = [];

    /**
     * @var the grid columns
     */
    private $_columns = [];

    /**
     * Initializes the widget
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->dataProvider) || !$this->dataProvider instanceof \yii\data\ActiveDataProvider) {
            throw new InvalidConfigException("The 'dataProvider' property must be set and must be an instance of '\\yii\\data\\ActiveDataProvider'.");
        }
        $this->initOptions();
        $this->registerAssets();
    }

    /**
     * Runs the widget
     *
     * @return string|void
     */
    public function run()
    {
        echo $this->renderGrid();
    }

    /**
     * Initializes the widget options
     */
    protected function initOptions()
    {
        $this->initDataColumns();
        $this->form->type = ActiveForm::TYPE_VERTICAL;

        if ($this->serialColumn !== false) {
            $this->initSerialColumn();
            $this->_columns = array_merge([$this->serialColumn], $this->_columns);
        }

        if ($this->actionColumn !== false) {
            $this->initActionColumn();
            $this->_columns = array_merge($this->_columns, [$this->actionColumn]);
        }

        if ($this->checkboxColumn !== false) {
            $this->initCheckboxColumn();
            $this->_columns = array_merge($this->_columns, [$this->checkboxColumn]);
        }

    }

    /**
     * Initializes the data columns
     */
    protected function initDataColumns()
    {
        foreach ($this->attributes as $attribute => $settings) {
            $label = isset($settings['label']) ? ['label' => $settings['label']] : [];
            $settings['label'] = '';
            if ($settings['type'] === self::INPUT_RAW && $settings['value'] instanceof \Closure) {
                $value = static::renderInput($this->form, $model, '[' . $index . ']' . $attribute, $settings);
            } else {
                $value = function ($model, $index, $widget) use($attribute, $settings) {
                    return static::renderInput($this->form, $model, '[' . $index . ']' . $attribute, $settings);
                };
            }
            $alignMiddle = ($settings['type'] == self::INPUT_RAW || $settings['type'] == self::INPUT_STATIC ||
                            $settings['type'] == self::INPUT_CHECKBOX || $settings['type'] == self::INPUT_RADIO);
            $this->_columns[] = [
                'attribute' => $attribute,
                'value' => $value,
                'format' => 'raw',
            ] + $label + ArrayHelper::getValue($settings, 'columnOptions', [])
            + ['vAlign' => $alignMiddle ? GridView::ALIGN_MIDDLE : GridView::ALIGN_TOP];
        }
    }

    /**
     * Initializes the serial column
     */
    protected function initSerialColumn()
    {
        if (!isset($this->serialColumn['class']) || !is_subclass_of($this->serialColumn['class'], '\kartik\grid\SerialColumn')) {
            $this->serialColumn['class'] = '\kartik\grid\SerialColumn';
        }
    }

    /**
     * Initializes the checkbox column
     */
    protected function initCheckboxColumn()
    {
        if (!isset($this->checkboxColumn['class']) || !is_subclass_of($this->checkboxColumn['class'], '\kartik\grid\CheckboxColumn')) {
            $this->checkboxColumn['class'] = '\kartik\grid\CheckboxColumn';
            $contentOptions = ArrayHelper::getValue($this->checkboxColumn, 'contentOptions', []);
            $headerOptions = ArrayHelper::getValue($this->checkboxColumn, 'headerOptions', []);
            Html::addCssClass($contentOptions, 'kv-row-select');
            Html::addCssClass($headerOptions, 'kv-all-select');
            $this->checkboxColumn['contentOptions'] = $contentOptions;
            $this->checkboxColumn['headerOptions'] = $headerOptions;
        }
    }

    /**
     * Initializes the action column
     */
    protected function initActionColumn()
    {
        if (!isset($this->actionColumn['class']) || !is_subclass_of($this->actionColumn['class'], '\kartik\grid\ActionColumn')) {
            $this->actionColumn['class'] = '\kartik\grid\ActionColumn';
        }
        $this->actionColumn['updateOptions'] = ['style' => 'display:none;'];
        $this->actionColumn +=  ['width' => '60px'];
    }

    /**
     * @return string the rendered gridview
     */
    protected function renderGrid()
    {
        $rowOptions = ArrayHelper::getValue($this->gridSettings, 'rowOptions', []);
        $this->options['id'] = ArrayHelper::getValue($this->gridSettings, 'id', $this->getId());
        Html::addCssClass($rowOptions, 'kv-tabform-row');
        $settings = [
            'id' => $this->options['id'],
            'dataProvider' => $this->dataProvider,
            'filterModel' => null,
            'dataColumnClass' => 'kartik\grid\DataColumn',
            'columns' => $this->_columns,
            'export' => false,
            'rowOptions' => $rowOptions
        ];
        $settings += $this->gridSettings + ['striped' => false, 'bordered' => false, 'hover' => true];
        return GridView::widget($settings);
    }

    protected function registerAssets() {
        $view = $this->getView();
        TabularFormAsset::register($view);
        $view->registerJs('selectRow($("#' . $this->options['id'] . '"), "' . $this->rowSelectedClass . '");');
    }
}