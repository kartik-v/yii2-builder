yii2-builder
============

A form builder extension that allows you to build both single view and multi-view/tabular forms for Yii Framework 2.0. The extension contains these widgets:

- Form
- FormGrid
- TabularForm

> NOTE: This extension depends on the [kartik-v/yii2-widgets](https://github.com/kartik-v/yii2-widgets) extension, which in turn depends on the
[yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2/tree/master/extensions/bootstrap) extension. Check the 
[composer.json](https://github.com/kartik-v/yii2-builder/blob/master/composer.json) for this extension's requirements and dependencies. 

## Latest Release
The latest version of the module is v1.6.0. Refer the [CHANGE LOG](https://github.com/kartik-v/yii2-builder/blob/master/CHANGE.md) for details.

## Form

`\kartik\builder\Form`

The Form Builder widget allows you to build a form through a configuration array. Key features available:

- Configure your form fields from a model extending `yii\base\model` or `yii\db\ActiveRecord`.
- Ability to support various Bootstrap 3.x form layouts. Uses the advanced [`kartik\widgets\ActiveForm`](http://demos.krajee.com/widget-details/active-form).
- Use Bootstrap column/builder layout styling by just supplying `columns` property.
- Build complex layouts (for example single, double, or multi columns in the same layout) - by reusing the widget for building your attributes.
- Tweak ActiveForm defaults to control field options, styles, templates, and layouts.
- Configure your own hints to display below each active field attribute.
- Various Bootstrap 3.x styling features are available by default. However, one can easily customize and theme it to one's liking using any CSS framework.
- Supports and renders HTML input types (uses [`kartik\widgets\ActiveField`](http://demos.krajee.com/widget-details/active-field)) including input widgets and more:
    - `INPUT_HIDDEN` or `hiddenInput`
    - `INPUT_TEXT` or `textInput`
    - `INPUT_TEXTAREA` or `textarea`
    - `INPUT_PASSWORD` or `passwordInput`
    - `INPUT_DROPDOWN_LIST` or `dropdownList`
    - `INPUT_LIST_BOX` or `listBox`
    - `INPUT_CHECKBOX` or `checkbox`
    - `INPUT_RADIO` or `radio`
    - `INPUT_CHECKBOX_LIST` or `checkboxList`
    - `INPUT_RADIO_LIST` or `radioList`
    - `INPUT_MULTISELECT` or `multiselect`
    - `INPUT_STATIC` or `staticInput`
    - `INPUT_FILE` or `fileInput`
    - `INPUT_HTML5` or `input`
    - `INPUT_WIDGET` or `widget`
    - `INPUT_RAW` or `raw` (any free text or html markup)

Refer the [documentation](http://demos.krajee.com/builder-details/form) for more details.

## FormGrid

`\kartik\builder\FormGrid`

Create bootstrap grid layouts in a snap. The Form Grid Builder widget offers an easy way to configure your form inputs as a bootstrap grid layout and a single array configuration. It basically uses 
multiple instances of the `\kartik\builder\Form` widget above to generate this grid. One needs to just setup the rows for the grid,
where each row will be an array configuration as needed by the `Form` widget. However, most of the common settings like `model`, `form`,
`columns` etc. can be defaulted at `FormGrid` widget level.

## Tabular Form 

`kartik\builder\TabularForm`

The tabular form allows you to update information from multiple models (typically used in master-detail forms). Key features

- Supports all input types as mentioned in the `Form` builder widget
- The widget works like a Yii GridView and uses an ActiveDataProvider to read the models information. 
- Supports features of the builderview like pagination and sorting.
- Allows you to highlight and select table rows
- Allows you to add and configure action buttons for each row.
- Configure your own hints to display below each active field attribute.
- Various Bootstrap 3.x styling features are available by default. However, one can easily customize and theme it to one's liking using any CSS framework.
- Advanced table styling, columns, and layout configuration by using the features available in the [`kartik\builder\GridView`]([`kartik\widgets\ActiveForm`](http://demos.krajee.com/builder) widget.
- One can easily read and manage the tabular input data using the `loadMultiple` and `validateMultiple` functions in `yii\base\Model`.

> NOTE: The TabularForm widget depends on and uses the [yii2-grid](http://demos.krajee.com/grid) module. Hence, the `gridview` module needs to be setup in your Yii configuration file.

```php
'modules' => [
   'gridview' =>  [
        'class' => '\kartik\grid\Module'
    ]
];
```

> IMPORTANT: You must follow one of the two options to setup your DataProvider or your columns to ensure primary key for each record is properly identified. 
- **Option 1 (preferred):** Setup your dataProvider query to use `indexBy` method to index your records by primary key. For example:
```php
$query = Model::find()->indexBy('id'); // where `id` is your primary key

$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);
```
- **Option 2 (alternate):** You can setup the primary key attribute as one of your columns with a form input type (and hide if needed) - so that the models are appropriately updated via <code>loadMultiple</code> method (even if you reorder or sort the columns). You must also set this attribute to be `safe` in your model validation rules. This is been depicted in the example below.
```php
'attributes'=>[
    'id'=>[ // primary key attribute
        'type'=>TabularForm::INPUT_HIDDEN, 
        'columnOptions'=>['hidden'=>true]
    ], 
 ]
```

### Demo
You can see detailed [documentation](http://demos.krajee.com/builder) on usage of the extension.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

> Note: You must set the `minimum-stability` to `dev` in the **composer.json** file in your application root folder before installation of this extension.

Either run

```
$ php composer.phar require kartik-v/yii2-builder "dev-master"
```

or add

```
"kartik-v/yii2-builder": "dev-master"
```

to the ```require``` section of your `composer.json` file.

## Usage

### Form
```php
use kartik\builder\Form;
$form = ActiveForm::begin();
echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 2,
    'attributes' => [
        'username' => ['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'Enter username...']],
        'password' => ['type'=>Form::INPUT_PASSWORD, 'options'=>['placeholder'=>'Enter password...']],
        'rememberMe' => ['type'=>Form::INPUT_CHECKBOX],
    ]
]);
ActiveForm::end();
```

### FormGrid
```php
use kartik\builder\Form;
use kartik\builder\FormGrid;
$form = ActiveForm::begin();
echo FormGrid::widget([
    'model' => $model,
    'form' => $form,
    'autoGenerateColumns' => true,
    'rows' => [
        [
            'attributes' => [
                'username' => ['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'Enter username...']],
                'password' => ['type'=>Form::INPUT_PASSWORD, 'options'=>['placeholder'=>'Enter password...']],
                'rememberMe' => ['type'=>Form::INPUT_CHECKBOX],
            ],
        ],
        [
            'attributes' => [
                'first_name' => ['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'Enter first name...']],
                'last_name' => ['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'Enter last name...']],
            ]
        ]
    ]
]);
ActiveForm::end();
```

### TabularForm
```php
use kartik\builder\TabularForm;
$form = ActiveForm::begin();
echo TabularForm::widget([
    'form' => $form,
    'dataProvider' => $dataProvider,
    'attributes' => [
        'name' => ['type' => TabularForm::INPUT_TEXT],
        'color' => [
            'type' => TabularForm::INPUT_WIDGET, 
            'widgetClass' => \kartik\widgets\ColorInput::classname()
        ],
        'author_id' => [
            'type' => TabularForm::INPUT_DROPDOWN_LIST, 
            'items'=>ArrayHelper::map(Author::find()->orderBy('name')->asArray()->all(), 'id', 'name')
        ],
        'buy_amount' => [
            'type' => TabularForm::INPUT_TEXT, 
            'options'=>['class'=>'form-control text-right'], 
            'columnOptions'=>['hAlign'=>GridView::ALIGN_RIGHT]
        ],
        'sell_amount' => [
            'type' => TabularForm::INPUT_STATIC, 
            'columnOptions'=>['hAlign'=>GridView::ALIGN_RIGHT]
        ],
    ],
    'gridSettings' => [
        'floatHeader' => true,
        'panel' => [
            'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-book"></i> Manage Books</h3>',
            'type' => GridView::TYPE_PRIMARY,
            'after'=> 
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Add New', 
                    $createUrl, 
                    ['class'=>'btn btn-success']
                ) . '&nbsp;' . 
                Html::a(
                    '<i class="glyphicon glyphicon-remove"></i> Delete', 
                    $deleteUrl, 
                    ['class'=>'btn btn-danger']
                ) . '&nbsp;' .
                Html::submitButton(
                    '<i class="glyphicon glyphicon-floppy-disk"></i> Save', 
                    ['class'=>'btn btn-primary']
                )
        ]
    ]     
]); 
ActiveForm::end(); 
```

## License

**yii2-builder** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.