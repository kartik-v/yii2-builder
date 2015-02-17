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
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Use an easy configuration option to render your form grid rows and columns
 * using \kartik\builder\Form widget.
 *
 * Usage:
 * ```
 *   use kartik\form\ActiveForm;
 *   use kartik\builder\FormGrid;
 *   $form = ActiveForm::begin($options); // $options is array for your form config
 *   echo FormGrid::widget([
 *       'model' => $model, // your model
 *       'form' => $form,
 *       'autoGenerateColumns' => true,
 *       'rows' => [
 *          [
 *              'attributes' => [
 *                  'username' => ['type' => Form::INPUT_TEXT, 'options'=> ['placeholder'=>'Enter username...']],
 *                  'password' => ['type' => Form::INPUT_PASSWORD],
 *               ]
 *          ],
 *          [
 *              'attributes' => [
 *                  'first_name' => ['type' => Form::INPUT_TEXT],
 *                  'last_name' => ['type' => Form::INPUT_PASSWORD],
 *               ]
 *          ]
 *       ]
 *   ]);
 *   ActiveForm::end();
 * ```
 *
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class FormGrid extends \yii\bootstrap\Widget
{
    /**
     * @var \yii\db\ActiveRecord|\yii\base\Model the model used for the form
     */
    public $model;

    /**
     * @var \yii\widgets\ActiveForm the form instance
     */
    public $form;

    /**
     * @var string the form name to be provided if not using with model
     * and ActiveForm
     */
    public $formName = null;

    /**
     * @var array the default settings that will be applied for all attributes. The array will be
     * configured similar to a single attribute setting value in the `Form::$attributes` array. One
     * will typically default markup and styling like `type`, `container`, `prepend`, `append` etc. The
     * settings at the `Form::$attributes` level will override these default settings.
     */
    public $attributeDefaults = [];

    /**
     * @var array, the grid rows containing form configuration elements
     */
    public $rows = [];

    /**
     * @var boolean, the number of columns for each row.
     * This property can be overridden at the `rows` level.
     */
    public $columns = 1;

    /**
     * @var boolean, calculate the number of columns automatically based on count of attributes
     * configured in the Form widget. Columns will be created max upto the Form::GRID_WIDTH.
     * This can be overridden at the rows level.
     */
    public $autoGenerateColumns = true;

    /**
     * @var string, the bootstrap device size for rendering each grid column. Defaults to `SIZE_SMALL`.
     * This property can be overridden at the `rows` level.
     */
    public $columnSize = Form::SIZE_SMALL;

    /**
     * @var array the HTML attributes for the grid columns. Applicable only if `$columns` is greater than 1.
     */
    public $columnOptions = [];

    /**
     * @var array the HTML attributes for the rows. Applicable only if `$columns` is greater than 1.
     * This property can be overridden at the `rows` level.
     */
    public $rowOptions = [];

    /**
     * @var array the HTML attributes for the field/attributes container. The following options are additionally
     *     recognized:
     * - `tag`: the HTML tag for the container. Defaults to `fieldset`.
     * This property can be overridden by `options` setting at the `rows` level.
     */
    public $fieldSetOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->rows) || !is_array($this->rows) || !is_array(current($this->rows))) {
            throw new InvalidConfigException(
                "The 'rows' property must be setup as an array of grid rows. Each row element must again be an array, where you must set the configuration properties as required by 'kartik\builder\Form'."
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();
        echo $this->getGridOutput();
    }

    /**
     * Generates the form grid layout
     * return string
     */
    protected function getGridOutput()
    {
        $output = '';
        foreach ($this->rows as $row) {
            $defaults = [
                'model' => $this->model,
                'form' => $this->form,
                'formName' => $this->formName,
                'columns' => $this->columns,
                'attributeDefaults' => $this->attributeDefaults,
                'autoGenerateColumns' => $this->autoGenerateColumns,
                'columnSize' => $this->columnSize,
                'columnOptions' => $this->columnOptions,
                'rowOptions' => $this->rowOptions,
                'options' => $this->fieldSetOptions,
            ];
            $config = array_replace_recursive($defaults, $row);
            $output .= Form::widget($config) . "\n";
        }
        return $output;
    }
}
