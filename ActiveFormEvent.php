<?php
/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @version   1.6.2
 */

namespace kartik\builder;

use yii\base\Event;

/**
 * Event for ActiveForm
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveFormEvent extends Event
{
    public $attribute;
    public $index;
    public $eventData;
}
