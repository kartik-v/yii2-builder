<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-builder
 * @version 1.5.0
 */

namespace kartik\builder;

/**
 * Asset bundle for \kartik\widgets\TabularForm
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class TabularFormAsset extends \kartik\base\AssetBundle
{

    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/tabular-form']);
        parent::init();
    }

}