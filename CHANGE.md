version 1.6.1
=============
**Date:** 17-Jun-2015

- (enh #52): Ability to render the entire form in static mode.
- (enh #55): Composite keys handling for tabular forms.
- Code formatting fixes and JS Lint changes.
- (bug #56): Fix INPUT_STATIC to work better with `TabularForm`.
- (enh #57): New property `staticOnly` for `Form` and `TabularForm`.
- (enh #58): New `staticValue` property as part of attributes configuration
- (enh #61): Fix array format attribute naming for non ActiveDataProvider.
- (enh #66): New input type `INPUT_HIDDEN_STATIC`
- (enh #67): New `ActiveFormEvent` class.
- (enh #68): Enhancements to Form sub attributes layout and styles.
- (enh #69): Enhance code methods and code format including PHP doc.
- (enh #70): Better default `actionColumn` settings.
- (enh #74): New `gridClass` property to allow using widget extending `kartik\grid\GridView`.
- (enh #77): Enhancements for horizontal form styles.
- (enh #81): Allow attribute settings properties to be setup as Closure in `TabularForm::attributes`.
- (enh #82): Add content before and after in Form builder.
- (enh #83): More correct Closure input parse for Form widget.
- (enh #87): Set composer version dependencies.

version 1.6.0
=============
**Date:** 28-Jan-2015

- (enh #38): Set dependencies to kartik\form\ActiveForm.
- Code format updates as per Yii2 coding style.
- (enh #39): Allow nested child attributes to be rendered together in one column.
- (enh #42): Add client validation plugin for nested attribute block.
- (enh #43): Enhance rendering of fields for horizontal, vertical, and inline forms.
- (enh #45): Add support for hidden input.
- (enh #46): Add new labelSpan property as part of builder attributes configuration.
- (enh #47): Default column size based on device size set in ActiveForm formConfig.
- (enh #49): Enhance docs for key attribute in TabularForm to ensure loadMultiple works right.
- (enh #50): Enable format ability for INPUT_STATIC types in TabularForm.
- (enh #51): Add support for rendering hidden inputs.

version 1.5.0
=============
**Date:** 03-Dec-2014

- (enh #33): Support all data providers extending from `yii\data\BaseDataProvider` for TabularForm widget.
- (enh #34): Enhance to support normal forms without model in addition to ActiveForm.
- (enh #35): Accelerate form building with new global attribute defaults.
- (enh #36): New additional options for attribute settings to control markup and styles.
    - `prepend`: string, (optional) any markup to prepend before the input
    - `append`: string, (optional) any markup to append before the input
    - `container`: array, (optional) HTML attributes for the `div` container to wrap the 
      field group (including input and label for all forms. This also includes error 
      & hint blocks for active forms).  If not set or empty, no container will be wrapped.
    - `inputContainer`: array, (optional) HTML attributes for the `div` container to wrap the 
      input control only. If not set or empty, no container will be wrapped. Will be applied 
      only when NOT using with active form.
    - `labelOptions`: array, (optional) the HTML attributes for the label. Will be applied only when NOT using with active form and only if label is set.
- New `FormTrait` added for better code reuse.

version 1.4.0
=============
**Date:** 01-Dec-2014

- (enh #30): Enhance Form Builder for horizontal forms and ability to customize labels.

version 1.3.0
=============
**Date:** 27-Nov-2014

- (enh #20): Fix code style and PHP documentation.
- (enh #28): Set `gridSettings['toggleData']` to false to prevent form nesting.
- (enh #29): Set default `rowHighlight` to true and `rowSelected` to TYPE_DANGER.
- Set copyright year to 2014.


version 1.2.0
=============
**Date:** 11-Nov-2014

- enh #23: Create `autoGenerateColumns` property for Form Builder
- enh #24: Create new `FormGrid` widget. Create bootstrap grid layouts in a snap.
- Set dependency on Krajee base component.
- Set release to stable.
- bug #25: Correct package dependencies.
- bug #26: Correct instance of ActiveForm validation for FormGrid.

version 1.1.0
=============
**Date:** 07-Jul-2014

- enh #13: Added ability to configure and display hints below each active field attribute.
- allow empty items for dropdown lists
- fixes #1 through #12.
- PSR 4 alias change


version 1.0.0
=============
**Date:** 01-May-2014

Initial release