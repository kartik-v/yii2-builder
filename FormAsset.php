<?php

/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version   1.6.0
 */

namespace kartik\builder;

use yii\web\AssetBundle;

/**
 * Asset bundle for \kartik\widgets\Form
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class FormAsset extends AssetBundle
{
    public $sourcePath = '@vendor/kartik-v/yii2-builder/assets';

    public $css = [
        'css/form.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}