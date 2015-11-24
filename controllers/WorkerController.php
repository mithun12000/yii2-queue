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
	 * @var string the default command action.
	 */
	public $defaultAction = 'worker';
	
	
	/**
	 * Run worker to consume job.
	 * For example,
	 *
	 * ~~~
	 * yii queue/worker     			# List all workers
	 * yii queue/worker worker1 3   	#run worker name producer1 with max 3 process
	 * yii queue/worker worker1 3 2  	#run worker name producer1 with max 3 process with min 2 process
	 * ~~~
	 * @param string $producer the producer class
	 * @param integer $max the maxumum number of producer process
	 * @param integer $min the minimum number of producer process
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 */
	public function actionWorker($worker = '', $max=1, $min=1)
	{
		if($worker){
			return $this->runWorker($worker);
		}else {
			$workerAr = $this->getWorkers();
			if (empty($workerAr)) {
				$this->stdout("No producer found.\n", Console::FG_GREEN);
				return self::EXIT_CODE_NORMAL;
			}
			$total = count($workerAr);
			
			foreach ($workerAr as $className) {
				$this->stdout("\t$className\n");
			}
			$this->stdout("\n");
			return 0;
		}
	}
	
	/**
	 * Create New Worker
	 * @inheritdoc
	 */
	public function actionCreate($name)
	{
		if (!preg_match('/^\w+$/', $name)) {
			throw new Exception('The migration name should contain letters, digits and/or underscore characters only.');
		}
		$name = 'm' . gmdate('ymd_His') . '_' . $name;
		$file = $this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php';
		if ($this->confirm("Create new migration '$file'?")) {
			$content = $this->renderFile(Yii::getAlias($this->templateFile), ['className' => $name]);
			file_put_contents($file, $content);
			$this->stdout("New migration created successfully.\n", Console::FG_GREEN);
		}
	}
}