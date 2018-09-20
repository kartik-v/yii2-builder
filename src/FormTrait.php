<?php

/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2018
 * @version   1.6.4
 */

namespace kartik\builder;

use yii\base\InvalidConfigException;
use yii\base\Model;
use kartik\form\ActiveForm;

/**
 * Trait for methods used in all the form builder widgets in `yii2-builder` and initialized within [[BaseForm]].
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
trait FormTrait
{
    /**
     * Checks base configuration and throws a configuration exception if invalid.
     *
     * @throws InvalidConfigException
     */
    protected function checkBaseConfig()
    {
        if (empty($this->form) && empty($this->formName)) {
            throw new InvalidConfigException(
                "The 'formName' property must be set when you are not using with ActiveForm."
            );
        }
        if (!empty($this->form) && !$this->form instanceof ActiveForm) {
            throw new InvalidConfigException(
                "The 'form' property must be an instance of '\\kartik\\widgets\\ActiveForm' or '\\kartik\\form\\ActiveForm'."
            );
        }
        if (empty($this->attributes)) {
            throw new InvalidConfigException("The 'attributes' array must be set.");
        }
    }

    /**
     * Checks the form configuration and throws a configuration exception if invalid.
     *
     * @throws InvalidConfigException
     */
    protected function checkFormConfig()
    {
        if (!$this->hasModel() && empty($this->formName)) {
            throw new InvalidConfigException(
                "Either the 'formName' has to be set or a valid 'model' property must be set extending from '\\yii\\base\\Model'."
            );
        }
        if (empty($this->formName) && (empty($this->form) || !$this->form instanceof ActiveForm)) {
            throw new InvalidConfigException(
                "The 'form' property must be set and must be an instance of '\\kartik\\form\\ActiveForm'."
            );
        }
    }

    /**
     * Check if a valid model is set for the object instance.
     *
     * @return boolean whether there is a valid model set.
     */
    protected function hasModel()
    {
        return isset($this->model) && $this->model instanceof Model;
    }
}
