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
use yii\helpers\Json;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;
use kartik\grid\GridView;

/**
 * A tabular form builder widget using kartik\form\ActiveForm.
 *
 * Usage:
 * ```
 *   use kartik\form\ActiveForm;
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
     * @var boolean highlight current row if checkbox is checked
     */
    public $rowHighlight = true;

    /**
     * @var string the class when a row is selected
     */
    public $rowSelectedClass = GridView::TYPE_DANGER;

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
        if (empty($this->dataProvider) || !$this->dataProvider instanceof \yii\data\BaseDataProvider) {
            throw new InvalidConfigException(
                "The 'dataProvider' property must be set and must be an instance of '\\yii\\data\\BaseDataProvider'."
            );
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
     *
     * @return void
     */
    protected function initOptions()
    {
        $this->initDataColumns();

        if (!empty($this->form)) {
            $this->form->type = ActiveForm::TYPE_VERTICAL;
        }

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
     *
     * @return void
     */
    protected function initDataColumns()
    {
        $formatter = ArrayHelper::getValue($this->gridSettings, 'formatter', Yii::$app->formatter);
        foreach ($this->attributes as $attribute => $settings) {
            $settings = array_replace_recursive($this->attributeDefaults, $settings);
            $label = isset($settings['label']) ? ['label' => $settings['label']] : [];
            $settings['label'] = false;
            if (isset($settings['type']) && $settings['type'] === self::INPUT_RAW) {
                $value = $settings['value'];
            } else {
                $value = function ($model, $key, $index, $widget) use ($attribute, $settings, $formatter) {
                    $type = ArrayHelper::getValue($settings, 'type', self::INPUT_RAW);
                    if ($type === self::INPUT_STATIC) {
                        $val = ArrayHelper::getValue($settings, 'value', null);
                        $format = ArrayHelper::getValue($settings, 'format', 'raw');
                        if ($val instanceof Closure) {
                            $val = call_user_func($val, $model, $key, $index, $widget);
                        } elseif ($model instanceof \yii\base\Model && !isset($settings['value'])) {
                            $val = Html::getAttributeValue($model, $attribute);
                        }
                        $val = $formatter->format($val, $format);
                        $opts = ArrayHelper::getValue($settings, 'options', []);
                        Html::addCssClass($opts, 'form-control-static');
                        return Html::tag('div', $val, $opts);
                    }
                    $i = empty($key) ? $index : $key;
                    if ($model instanceof \yii\base\Model) {
                        $input = static::renderActiveInput(
                            $this->form,
                            $model,
                            "[{$i}]{$attribute}",
                            $settings
                        );
                    } else {
                        $models = $this->dataProvider->getModels();
                        $settings['value'] = empty($models[$index][$attribute]) ? null : $models[$index][$attribute];
                        $input = static::renderInput("{$this->formName}[{$i}]{$attribute}", $settings);
                    }
                    return $input;
                };
            }
            $alignMiddle = ($settings['type'] == self::INPUT_RAW || $settings['type'] == self::INPUT_STATIC ||
                $settings['type'] == self::INPUT_CHECKBOX || $settings['type'] == self::INPUT_RADIO);
            $this->_columns[] = ArrayHelper::merge(
                ['vAlign' => $alignMiddle ? GridView::ALIGN_MIDDLE : GridView::ALIGN_TOP],
                ArrayHelper::getValue($settings, 'columnOptions', []),
                $label,
                ['attribute' => $attribute, 'value' => $value, 'format' => 'raw']
            );
        }
    }

    /**
     * Initializes the serial column
     *
     * @return void
     */
    protected function initSerialColumn()
    {
        if (!isset($this->serialColumn['class']) ||
            !is_subclass_of($this->serialColumn['class'], '\kartik\grid\SerialColumn')
        ) {
            $this->serialColumn['class'] = '\kartik\grid\SerialColumn';
        }
    }

    /**
     * Initializes the checkbox column
     *
     * @return void
     */
    protected function initCheckboxColumn()
    {
        if (!isset($this->checkboxColumn['class']) ||
            !is_subclass_of($this->checkboxColumn['class'], '\kartik\grid\CheckboxColumn')
        ) {
            $this->checkboxColumn['class'] = '\kartik\grid\CheckboxColumn';
            $this->checkboxColumn['rowHighlight'] = $this->rowHighlight;
            $this->checkboxColumn['rowSelectedClass'] = $this->rowSelectedClass;
        }
    }

    /**
     * Initializes the action column
     *
     * @return void
     */
    protected function initActionColumn()
    {
        if (!isset($this->actionColumn['class']) ||
            !is_subclass_of($this->actionColumn['class'], '\kartik\grid\ActionColumn')
        ) {
            $this->actionColumn['class'] = '\kartik\grid\ActionColumn';
        }
        $this->actionColumn['updateOptions'] = ['style' => 'display:none;'];
        $this->actionColumn = ArrayHelper::merge(['width' => '60px'], $this->actionColumn);
    }

    /**
     * Render the grid content
     *
     * @return string the rendered gridview
     */
    protected function renderGrid()
    {
        $rowOptions = [];
        if (isset($this->gridSettings['rowOptions'])) {
            $rowOptions = $this->gridSettings['rowOptions'];
        }
        if (is_array($rowOptions)) {
            Html::addCssClass($rowOptions, 'kv-tabform-row');
        }
        $this->options['id'] = ArrayHelper::getValue($this->gridSettings, 'id', $this->getId());
        $settings = [
            'id' => $this->options['id'],
            'dataProvider' => $this->dataProvider,
            'filterModel' => null,
            'dataColumnClass' => 'kartik\grid\DataColumn',
            'columns' => $this->_columns,
            'export' => false,
            'toggleData' => false,
            'rowOptions' => $rowOptions
        ];
        $settings = ArrayHelper::merge(
            ['striped' => false, 'bordered' => false, 'hover' => true],
            $this->gridSettings,
            $settings
        );
        return GridView::widget($settings);
    }

    /**
     * Registers widget assets
     *
     * @return void
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        TabularFormAsset::register($view);
    }
}