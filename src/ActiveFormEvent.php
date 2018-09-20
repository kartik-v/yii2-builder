<?php
/**
 * @package   yii2-builder
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2018
 * @version   1.6.4
 */

namespace kartik\builder;

use yii\base\Event;

/**
 * ActiveFormEvent is the event class for [[\kartik\form\ActiveForm]]. It encapsulates parameters that can be
 * used as part of event handling to manipulate form builder and tabular form behavior.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since  1.0
 */
class ActiveFormEvent extends Event
{
    /**
     * @var string the model attribute name used in the form
     */
    public $attribute;
    /**
     * @var integer the row index of the attribute in the bootstrap grid layout.
     */
    public $index;
    /**
     * @var array any additional event data that can be passed by the event handler as key value pairs.
     */
    public $eventData;
}
