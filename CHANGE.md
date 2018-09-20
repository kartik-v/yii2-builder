Change Log: `yii2-builder`
==========================

## Version 1.6.4

**Date:** 20-Sep-2018

- Enhancements to support Bootstrap v4.x.
- Move all source code to `src` directory.

## Version 1.6.3

**Date:** 29-Aug-2017

- (enh #127): Better empty content validation.
- (enh #126): Enhance hidden input rendering to not display additional markup.
- Update copyright year to current.
- Chronological sorting of issues and enhancements in CHANGE.log.
- Include github contribution and issue/PR log templates.

## Version 1.6.2

**Date:** 19-Nov-2016

- Enhance and complete PHP documentation for all classes in the extension.
- (enh #112): Correct prepend and append for INPUT_STATIC.
- (enh #108, #110): Add support for configuring attribute visibility.
- (enh #95): Add functionality for Bootstrap Checkbox Button Group and Radio Button Group.

## Version 1.6.1

**Date:** 17-Jun-2015

- (enh #87): Set composer ## Version dependencies.
- (enh #83): More correct Closure input parse for Form widget.
- (enh #82): Add content before and after in Form builder.
- (enh #81): Allow attribute settings properties to be setup as Closure in `TabularForm::attributes`.
- (enh #77): Enhancements for horizontal form styles.
- (enh #74): New `gridClass` property to allow using widget extending `kartik\grid\GridView`.
- (enh #70): Better default `actionColumn` settings.
- (enh #69): Enhance code methods and code format including PHP doc.
- (enh #68): Enhancements to Form sub attributes layout and styles.
- (enh #67): New `ActiveFormEvent` class.
- (enh #66): New input type `INPUT_HIDDEN_STATIC`
- (enh #61): Fix array format attribute naming for non ActiveDataProvider.
- (enh #58): New `staticValue` property as part of attributes configuration
- (enh #57): New property `staticOnly` for `Form` and `TabularForm`.
- (bug #56): Fix INPUT_STATIC to work better with `TabularForm`.
- Code formatting fixes and JS Lint changes.
- (enh #55): Composite keys handling for tabular forms.
- (enh #52): Ability to render the entire form in static mode.

## Version 1.6.0

**Date:** 28-Jan-2015

- (enh #50): Enable format ability for INPUT_STATIC types in TabularForm.
- (enh #51): Add support for rendering hidden inputs.
- (enh #49): Enhance docs for key attribute in TabularForm to ensure loadMultiple works right.
- (enh #47): Default column size based on device size set in ActiveForm formConfig.
- (enh #46): Add new labelSpan property as part of builder attributes configuration.
- (enh #45): Add support for hidden input.
- (enh #43): Enhance rendering of fields for horizontal, vertical, and inline forms.
- (enh #42): Add client validation plugin for nested attribute block.
- (enh #39): Allow nested child attributes to be rendered together in one column.
- Code format updates as per Yii2 coding style.
- (enh #38): Set dependencies to `kartik\form\ActiveForm`.

## Version 1.5.0

**Date:** 03-Dec-2014

- New `FormTrait` added for better code reuse.
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
- (enh #35): Accelerate form building with new global attribute defaults.
- (enh #34): Enhance to support normal forms without model in addition to ActiveForm.
- (enh #33): Support all data providers extending from `yii\data\BaseDataProvider` for TabularForm widget.

## Version 1.4.0

**Date:** 01-Dec-2014

- (enh #30): Enhance Form Builder for horizontal forms and ability to customize labels.

## Version 1.3.0

**Date:** 27-Nov-2014

- Update copyright year to 2014.
- (enh #29): Set default `rowHighlight` to true and `rowSelected` to TYPE_DANGER.
- (enh #28): Set `gridSettings['toggleData']` to false to prevent form nesting.
- (enh #20): Fix code style and PHP documentation.


## Version 1.2.0

**Date:** 11-Nov-2014

- (bug #26): Correct instance of ActiveForm validation for FormGrid.
- (bug #25): Correct package dependencies.
- Set release to stable.
- Set dependency on Krajee base component.
- (enh #24): Create new `FormGrid` widget. Create bootstrap grid layouts in a snap.
- (enh #23): Create `autoGenerateColumns` property for Form Builder

## Version 1.1.0

**Date:** 07-Jul-2014

- PSR 4 alias change
- allow empty items for dropdown lists
- (enh #13): Added ability to configure and display hints below each active field attribute.
- (enh #1 through #12): Various optimization fixes.

## Version 1.0.0

**Date:** 01-May-2014

Initial release