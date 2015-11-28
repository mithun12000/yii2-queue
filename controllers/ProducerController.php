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
	public $templateFile = '@mithun/queue/producer/template.php';
	
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
	 * @return boolean the status of the action execution. 0 means normal, other values mean abnormal.
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
	 * Creates a new Producer
	 *
	 * This command creates a new producer using the available producer template.
	 * After using this command, developers should modify the created producer
	 * skeleton by filling up the actual producer logic.
	 *
	 * @param string $name the name of the new producer. This should only contain
	 * letters, digits and/or underscores.
	 * @param string $path the path for generating new producer.
	 * This will be path alias (e.g. "@app") default "@app"
	 * @throws Exception if the name argument is invalid.
	 */
	public function actionCreate($name,$path = '@app')
	{
		$this->createPubsub($name,'Producer',$path.'/producers');
	}
}