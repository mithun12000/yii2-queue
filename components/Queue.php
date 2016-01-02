<?php
/**
 * 
 */
namespace mithun\queue\components;;

use Yii;
use yii\base\Component;
use mithun\queue\services\QueueInterface;
use yii\base\InvalidConfigException;

/**
 * QueueEvent
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class Queue extends Component implements QueueInterface
{	
	const BEFORE_PUSH 		= 'before_push';
	const BEFORE_POP 		= 'before_pop';
	const BEFORE_PURGE 		= 'before_purge';
	const BEFORE_RELEASE 	= 'before_release';
	const BEFORE_DELETE 	= 'before_delete';
	
	const AFTER_PUSH 		= 'after_push';
	const AFTER_POP 		= 'after_pop';
	const AFTER_PURGE 		= 'after_purge';
	const AFTER_RELEASE 	= 'after_release';
	const AFTER_DELETE 		= 'after_delete';
	
	/**
	 * Driver Object
	 * @var QueueInterface
	 */
	public $driver;
	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		if(!$this->driver){
			throw new InvalidConfigException("Driver is required.");
		}
		
		$this->driver = Yii::createObject($this->driver);
	}
	
	/**
	 * Event triggering code
	 * @param string $event_name
	 */
	public function trigger_event($event_name){
		$event = new QueueEvent();
		$event->queue = $this->driver;
		$this->trigger($event_name, $event);
		return $event->is_valid;
	}
	
	/**
	 * @inheritdoc
	 */
	public function push($payload, $queue, $delay = 0){
		if($this->trigger_event(self::BEFORE_PUSH)){
			$returnId = $this->driver->push($payload, $queue,$delay);
			$this->trigger_event(self::AFTER_PUSH);
			return $returnId;
		}
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function pop($queue){
		if($this->trigger_event(self::BEFORE_POP)){
			$job = $this->driver->pop($queue);
			$this->trigger_event(self::AFTER_POP);
			return $job;
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function purge($queue) {
		if($this->trigger_event(self::BEFORE_PURGE)){
			$this->driver->purge($queue);
			$this->trigger_event(self::AFTER_PURGE);
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function release(array $message, $delay = 0){
		if($this->trigger_event(self::BEFORE_RELEASE)){
			$this->driver->release($message,$delay);
			$this->trigger_event(self::AFTER_RELEASE);
		}
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function delete(array $message){
		if($this->trigger_event(self::BEFORE_DELETE)){
			$this->driver->delete($message);
			$this->trigger_event(self::AFTER_DELETE);
		}
	}
}