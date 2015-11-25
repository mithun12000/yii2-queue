<?php
/**
 * 
 */
namespace mithun\queue\controllers;

use Yii;
use yii\console\Exception;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Base Queue Controller
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
abstract class BaseQueueController extends Controller
{	
	/**
	 * @var array the directory storing the worker classes. This can be either
	 * a path alias or a directory.
	 */
	protected  $workerPath = ['@app/workers'];
	
	/**
	 * @var array the directory storing the producer classes. This can be either
	 * a path alias or a directory.
	 */
	protected  $producerPath = ['@app/producers'];
	
	/**
	 * @var string the template file for generating new worker/producer.
	 * This can be either a path alias (e.g. "@app/worker/template.php")
	 * or a file path.
	 */
	public $templateFile;
	
	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$exclude = ['module'=>['debug','gii','metadata','queue']];
		foreach (Yii::$app->getModules() as $id => $child) {
			if (($child = Yii::$app->getModule($id)) !== null) {
				if(!in_array($child->id, $exclude['module'])){
					$path = '@' . str_replace('\\', '/', trim($child->controllerNamespace, '\\'));
					$path = str_replace('commands', '[PUBSUB]', $path);
					$path = str_replace('controllers', '[PUBSUB]', $path);
					
					$this->workerPath[] = str_replace('[PUBSUB]', 'workers', $path);
					$this->producerPath[] = str_replace('[PUBSUB]', 'producers', $path);
				}
			}
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function options($actionID)
	{
		return array_merge(
				parent::options($actionID),
				['migrationPath'], // global for all actions
				($actionID === 'create') ? ['templateFile'] : [] // action create
				);
	}
	
	
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * It checks the existence of the [[migrationPath]].
	 * @param \yii\base\Action $action the action to be executed.
	 * @throws Exception if directory specified in migrationPath doesn't exist and action isn't "create".
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			$path = Yii::getAlias($this->migrationPath);
			if (!is_dir($path)) {
				if ($action->id !== 'create') {
					throw new Exception("Migration failed. Directory specified in migrationPath doesn't exist: {$this->migrationPath}");
				}
				FileHelper::createDirectory($path);
			}
			$this->migrationPath = $path;
			$version = Yii::getVersion();
			$this->stdout("Yii Migration Tool (based on Yii v{$version})\n\n");
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Creates a new producer / worker.
	 *
	 * This command creates a new producer / worker using the available migration template.
	 * After using this command, developers should modify the created migration
	 * skeleton by filling up the actual migration logic.
	 *
	 * @param string $name the name of the new migration. This should only contain
	 * letters, digits and/or underscores.
	 * @throws Exception if the name argument is invalid.
	 */
	abstract public function actionCreate($name);
	
	
	/**
	 * run worker class
	 * @param string $class the worker class name
	 * @return boolean whether the migration is successful
	 */
	protected function runWorker($class)
	{
		
	}
	
	/**
	 * run producer
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	protected function runProducer($class)
	{
	
	}
	
	/**
	 * Creates a new Producer / Worker instance.
	 * @param string $class the migration class name
	 * @param string $path The file path
	 * @param string $namespace namespace of the file
	 */
	protected function createPubsub($class,$path,$namespace)
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
	
	/**
	 * Read directory for producer / worker file
	 * @param string $path
	 */
	protected function readPubSub($path){
		$pubsub = [];
		$path = Yii::getAlias($path);
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			if (preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/', $file, $matches) && is_file($path . DIRECTORY_SEPARATOR . $file)) {
				$pubsub[] = $matches[1];
			}
		}
		closedir($handle);
		return $pubsub;
	}
	
	/**
	 * get all worker class name
	 */
	protected function getWorkers(){
		$worker = [];
		foreach($this->workerPath as $path_alias){
			$worker = array_merge($worker,$this->readPubSub($path_alias));
		}
		sort($worker);
		return $worker;
	}
	
	/**
	 * Get all producer class name
	 */
	protected function getProducer(){
		$producer = [];
		foreach($this->workerPath as $path_alias){
			$producer = array_merge($worker,$this->readPubSub($path_alias));
		}
		sort($producer);
		return $producer;
	}
}
