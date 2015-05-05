<?php

/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @version   1.6.1
 */
namespace kartik\builder;

use Yii;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use \Closure;

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
 * Most of each property in the attribute settings array (except `label` & `columnOptions`) can be
 * also setup as a Closure callback i.e. `function ($model, $key, $index, $widget)`. For example:
 * ```
 * echo TabularForm::widget([
 *       'model' => $model, // your model
 *       'form' => $form,
 *       'attributes' => [
 *           'id' => [
 *              'type' => function($model, $key, $index, $widget) {
 *                  return (!$model->active) ? TabularForm::INPUT_HIDDEN : TabularForm::INPUT_STATIC]
 *              }
 *           ],
 *       ]
 * ]);
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
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
     * @var string the separator for the composite keys available as an array
     */
    public $compositeKeySeparator = '_';

    /**
     * @var string the class when a row is selected
     */
    public $rowSelectedClass = GridView::TYPE_DANGER;

    /**
     * @var string the namespaced GridView class name. Defaults to '\kartik\grid\GridView'.
     * Any other class set here must extend from '\kartik\grid\GridView'.
     */
    public $gridClass;

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
    public $actionColumn = [
        'updateOptions' => ['style' => 'display:none'],
        'width' => '60px'
    ];

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
        $dp = static::slash(BaseDataProvider::className());
        if (empty($this->dataProvider) || !$this->dataProvider instanceof BaseDataProvider) {
            throw new InvalidConfigException("The 'dataProvider' property must be set and must be an instance of '{$dp}'.");
        }
        $kvGrid = static::slash(GridView::classname());
        if (empty($this->gridClass)) {
            $this->gridClass = $kvGrid;
        } elseif ($this->gridClass !== $kvGrid && !is_subclass_of($this->gridClass, $kvGrid)) {
            throw new InvalidConfigException("The 'gridClass' must be a class which extends from '{$kvGrid}'.");
        }
        $this->initOptions();
        $this->registerAssets();
    }

    /**
     * Prepends with a back slash if necessary for full namespace validation.
     *
     * @param string $str the input string
     *
     * @return string
     */
    protected static function slash($str = '')
    {
        if (empty($str) || substr($str, 1) == "\\") {
            return $str;
        }
        return "\\" . $str;
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

        $this->initColumn('serial');
        $this->initColumn('action');
        $this->initColumn('checkbox');
    }

    /**
     * Initializes special columns
     *
     * @param string $type the grid column type (one of 'serial', 'action', 'checkbox')
     */
    protected function initColumn($type)
    {
        $col = $type . 'Column';
        if ($this->$col === false) {
            return;
        }
        $func = 'init' . ucfirst($type) . 'Column';
        $this->$func();
        if ($type === 'serial') {
            $this->_columns = ArrayHelper::merge([$this->$col], $this->_columns);
        } else {
            $this->_columns = ArrayHelper::merge($this->_columns, [$this->$col]);
        }
    }

    /**
     * Checks if a grid column is set correctly
     *
     * @param string $type the grid column type (one of 'serial', 'action', 'checkbox')
     *
     * @return bool
     */
    protected function isColumnSet($type)
    {
        $target = "\\kartik\\grid\\" . ucfirst($type) . "Column";
        $param = $type . 'Column';
        $col = $this->$param;
        if (empty($col)) {
            $col = [];
        }
        $class = ArrayHelper::getValue($col, 'class', '');
        $out = !empty($class) && is_subclass_of($class, $target);
        if (!$out) {
            $col['class'] = $target;
            $this->$param = $col;
        }
        return $out;
    }

    /**
     * Initializes the serial column
     *
     * @return void
     */
    protected function initSerialColumn()
    {
        $this->isColumnSet('serial');
    }

    /**
     * Initializes the checkbox column
     *
     * @return void
     */
    protected function initCheckboxColumn()
    {
        if (!$this->isColumnSet('checkbox')) {
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
        $this->isColumnSet('action');
    }

    /**
     * Generates the static input
     *
     * @param string             $type the static input type
     * @param yii\base\Model     $model
     * @param mixed              $key the key
     * @param int                $index the zero based index of the item in dataProvider
     * @param array              $settings the attribute settings
     * @param string             $attribute the attribute
     * @param yii\i18n\Formatter $formatter the formatter instance
     *
     * @return string
     */
    protected function getStaticInput($type, $model, $key, $index, $settings, $attribute, $formatter)
    {
        $format = ArrayHelper::getValue($settings, 'format', 'raw');
        if ($type === self::INPUT_HIDDEN_STATIC) {
            $options = ArrayHelper::getValue($settings, 'hiddenStaticOptions', []);
        } else {
            $options = ArrayHelper::getValue($settings, 'options', []);
        }
        $val = null;
        if (isset($settings['staticValue'])) {
            $val = $settings['staticValue'];
        } else {
            if (isset($settings['value'])) {
                $val = $settings['value'];
            } elseif ($model instanceof \yii\base\Model) {
                $val = Html::getAttributeValue($model, $attribute);
            } elseif (($models = $this->dataProvider->getModels()) && !empty($models[$index][$attribute])) {
                $val = $models[$index][$attribute];
            }
        }
        $val = $formatter->format($val, $format);
        Html::addCssClass($options, 'form-control-static');
        return Html::tag('div', $val, $options);
    }

    /**
     * Checks if setting is of valid type and throws exception if not.
     *
     * @param string $attr
     * @param array  $settings
     * @param string $key
     * @param string $type
     *
     * @throws InvalidConfigException
     */
    protected static function checkValidSetting($attr, $settings, $key, $type)
    {
        if (!isset($settings[$key])) {
            return;
        }
        $validateFunc = "is_{$type}";
        if (!$validateFunc($settings[$key])) {
            throw new InvalidConfigException("You must set the 'settings[\"{$key}\"]' property for " .
                "'{$attr}' attribute as a valid {$type} (no Closure method is supported).");
        }
    }

    /**
     * Generates a callable grid cell value
     *
     * @param string $attribute
     * @param mixed  $settings
     *
     * @return callable
     */
    protected function getCellValue($attribute, $settings)
    {
        $formatter = ArrayHelper::getValue($this->gridSettings, 'formatter', Yii::$app->formatter);
        return function ($model, $key, $index, $widget) use ($attribute, $settings, $formatter) {
            $staticInput = '';
            foreach ($settings as $k => $v) {
                if ($v instanceof Closure) {
                    $settings[$k] = call_user_func($v, $model, $key, $index, $widget);
                }
            }
            $type = ArrayHelper::getValue($settings, 'type', self::INPUT_RAW);
            if ($type === self::INPUT_STATIC || $this->staticOnly || $type === self::INPUT_HIDDEN_STATIC) {
                $staticInput = $this->getStaticInput($type, $model, $key, $index, $settings, $attribute, $formatter);
                if ($type !== self::INPUT_HIDDEN_STATIC) {
                    return $staticInput;
                }
            }
            $i = empty($key) ? $index : (is_array($key) ? implode($this->compositeKeySeparator, $key) : $key);
            $options = ArrayHelper::getValue($settings, 'options', []);
            if ($model instanceof \yii\base\Model) {
                if ($type === self::INPUT_HIDDEN_STATIC) {
                    return $staticInput . Html::activeHiddenInput($model, "[{$i}]{$attribute}", $options);
                }
                return static::renderActiveInput($this->form, $model, "[{$i}]{$attribute}", $settings);

            } else {
                $models = $this->dataProvider->getModels();
                $settings['value'] = empty($models[$index][$attribute]) ? null : $models[$index][$attribute];
                if ($type === self::INPUT_HIDDEN_STATIC) {
                    return $staticInput .
                    Html::hiddenInput("{$this->formName}[{$i}][{$attribute}]", $settings['value'], $options);
                }
                return static::renderInput("{$this->formName}[{$i}][{$attribute}]", $settings);
            }
        };
    }

    /**
     * Initializes the data columns
     *
     * @throws \yii\base\InvalidConfigException
     *
     * @return void
     */
    protected function initDataColumns()
    {
        foreach ($this->attributes as $attribute => $settings) {
            static::checkValidSetting($attribute, $settings, 'label', 'string');
            static::checkValidSetting($attribute, $settings, 'columnOptions', 'array');
            $settings = array_replace_recursive($this->attributeDefaults, $settings);
            $label = isset($settings['label']) ? ['label' => $settings['label']] : [];
            $settings['label'] = false;
            $columnOptions = ArrayHelper::getValue($settings, 'columnOptions', []);
            if (!$this->staticOnly && isset($settings['type']) && $settings['type'] === self::INPUT_RAW) {
                $value = $settings['value'];
            } else {
                $value = $this->getCellValue($attribute, $settings);
            }
            // auto alignment for certain input types - if the `type` is setup as a Closure, then the
            // following condition will not work, and one would need to set the alignment manually.
            $alignMiddle = isset($settings['type']) && ($settings['type'] == self::INPUT_RAW ||
                    $settings['type'] == self::INPUT_STATIC || $settings['type'] == self::INPUT_CHECKBOX ||
                    $settings['type'] == self::INPUT_RADIO);
            $this->_columns[] = ArrayHelper::merge(
                ['vAlign' => $alignMiddle ? GridView::ALIGN_MIDDLE : GridView::ALIGN_TOP],
                $columnOptions,
                $label,
                ['attribute' => $attribute, 'value' => $value, 'format' => 'raw']
            );
        }
    }

    /**
     * Render the grid content
     *
     * @return string the rendered gridview
     */
    protected function renderGrid()
    {
        $rowOptions = [];
        $gridClass = $this->gridClass;
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
        return $gridClass::widget($settings);
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