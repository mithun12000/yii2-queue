<?php
/**
 * 
 */
namespace mithun\queue\components;;

use yii\base\Event;
use mithun\queue\services\QueueInterface;
/**
 * QueueEvent
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class QueueEvent extends Event
{	
	/**
	 * Queue Object
	 * @var QueueInterface
	 */
	public $queue;
	
	public $is_valid = true;
}