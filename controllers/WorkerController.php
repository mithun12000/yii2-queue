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
 * Manage Worker Job
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class WorkerController extends BaseQueueController
{
	/**
	 * @var string
	 */
	public $templateFile = '@mithun/queue/worker/template.php';
	/**
	 * @var string the default command action.
	 */
	public $defaultAction = 'worker';
	
	
	/**
	 * Run worker to consume job.
	 * For example,
	 *
	 * ~~~
	 * yii queue/worker     			# List all workers
	 * yii queue/worker worker1 3   	#run worker name worker1 with max 3 process
	 * yii queue/worker worker1 3 2  	#run worker name producer1 with max 3 process with min 2 process
	 * ~~~
	 * @param string $worker the worker class
	 * @param integer $max the maxumum number of worker process
	 * @param integer $min the minimum number of worker process
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 */
	public function actionWorker($worker = '', $max=1, $min=1)
	{
		if($worker){
			return $this->runWorker($worker);
		}else {
			return $this->getPubsub($this->workerPath,'No worker found');
		}
	}
	
	/**
	 * Creates a new Worker
	 *
	 * This command creates a new worker using the available worker template.
	 * After using this command, developers should modify the created worker
	 * skeleton by filling up the actual worker logic.
	 *
	 * @param string $name the name of the new worker. This should only contain
	 * letters, digits and/or underscores.
	 * @param string $path the path for generating new worker.
	 * This will be path alias (e.g. "@app") default "@app"
	 * @throws Exception if the name argument is invalid.
	 */
	public function actionCreate($name,$path = '@app')
	{
		$this->createPubsub($name,'Worker',$path.'/workers');
	}
}