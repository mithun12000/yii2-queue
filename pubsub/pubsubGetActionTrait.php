<?php
/**
 *
*/
namespace mithun\queue\pubsub;

use Yii;
use Arara\Process\Action\Callback;
use Arara\Process\Action\Action;


/**
 * pubsubGetActionTrait
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
trait pubsubGetActionTrait{

	/**
	 * Get Action
	 */
	protected function getAction(){
		$action = new Callback([$this, 'run']);
	
		$action->bind(Action::EVENT_START, function (Context $context) {
			$this->stdout(json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))."\n", Console::FG_YELLOW);
		});
	
		$action->bind(Action::EVENT_ERROR, function (Context $context) {
			$this->stdout(json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))."\n", Console::FG_RED);
		});
	
		$action->bind(Action::EVENT_SUCCESS, function (Context $context) {
			$this->stdout(json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))."\n", Console::FG_BLUE);
		});
	
		$action->bind(Action::EVENT_FAILURE, function (Context $context) {
			$this->stdout(json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))."\n", Console::FG_RED);
		});
	
		$action->bind(Action::EVENT_FINISH, function (Context $context) {
			$this->stdout(json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))."\n", Console::FG_GREEN);
		});
		
		return $action;
	}
}