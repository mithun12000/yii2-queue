<?php
/**
 * 
 */
namespace mithun\queue\controllers;

use Yii;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Manage Producer Job
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class ProducerController extends BaseQueueController
{
	/**
	 * @var string
	 */
	public $templateFile = '@mithun/queue/producer/template';
	
	/**
	 * @var string the default command action.
	 */
	public $defaultAction = 'producer';
	
	/**
	 * Create Job in message queue
	 * For example,
	 *
	 * ~~~
	 * yii queue/producer     # List all producer
	 * ~~~
	 * @param string $producer the producer class
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 */
	public function actionProducer($producer = '')
	{
		if($producer){
			return $this->runProducer($producer);
		}else {
			$producerAr = $this->getProducer();
			if (empty($producerAr)) {
				$this->stdout("No producer found.\n", Console::FG_GREEN);
				return self::EXIT_CODE_NORMAL;
			}
			$total = count($producerAr);
			
			foreach ($producerAr as $className) {
				$this->stdout("\t$className\n");
			}
			$this->stdout("\n");
			return 0;
		}
	}
	
	/**
	 * Create New Producer
	 * @inheritdoc
	 */
	public function actionCreate($name,$path = '@app/producers')
	{
		$this->createPubsub($name,$path,$namespace);		
	}
}