<?php

/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2022
 * @version   1.6.9
 */

namespace kartik\builder;

use kartik\base\AssetBundle;

/**
 * Asset bundle for the [[TabularForm]] widget.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 */
class TabularFormAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/tabular-form']);
        parent::init();
    }
}
