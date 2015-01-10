<?php

/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version   1.6.0
 */
namespace kartik\builder;

use yii\base\InvalidConfigException;

/**
 * Trait for all form builder widgets in this extension
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
trait FormTrait
{
    /**
     * Checks base config
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
        if (!empty($this->form) && !$this->form instanceof \kartik\form\ActiveForm) {
            throw new InvalidConfigException(
                "The 'form' property must be an instance of '\\kartik\\widgets\\ActiveForm' or '\\kartik\\form\\ActiveForm'."
            );
        }
        if (empty($this->attributes)) {
            throw new InvalidConfigException("The 'attributes' array must be set.");
        }
    }

    /**
     * Checks config for Form widgets
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
        if (empty($this->formName) && (empty($this->form) || !$this->form instanceof \kartik\form\ActiveForm)) {
            throw new InvalidConfigException(
                "The 'form' property must be set and must be an instance of '\\kartik\\form\\ActiveForm'."
            );
        }
    }

    /**
     * Check if a valid model is set for the object instance
     *
     * @return boolean
     */
    protected function hasModel()
    {
        return isset($this->model) && $this->model instanceof \yii\base\Model;
    }
}