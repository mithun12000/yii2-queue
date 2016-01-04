<?php
/**
 *
*/
namespace mithun\queue\pubsub;

use Yii;
use Arara\Process\Action\Callback;
use Arara\Process\Action\Action;
use Arara\Process\Context;
use yii\helpers\Console;

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
			Yii::$app->controller->stdout("Started ".json_encode($context->toArray())."\n", Console::FG_YELLOW);
		});
		
		$action->bind(Action::EVENT_ERROR, function (Context $context) {
			Yii::$app->controller->stdout("Error ".json_encode($context->toArray())."\n", Console::FG_RED);
		});
	
		$action->bind(Action::EVENT_SUCCESS, function (Context $context) {
			Yii::$app->controller->stdout("Success ".json_encode($context->toArray())."\n", Console::FG_BLUE);
		});
	
		$action->bind(Action::EVENT_FAILURE, function (Context $context) {
			Yii::$app->controller->stdout("Failed ".json_encode($context->toArray())."\n", Console::FG_RED);
		});
	
		$action->bind(Action::EVENT_FINISH, function (Context $context) {
			Yii::$app->controller->stdout("finish ".json_encode($context->toArray())."\n\n\n", Console::FG_GREEN);
		});
		
		
		return $action;
	}
}