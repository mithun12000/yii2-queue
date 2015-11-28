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
	 * @param \yii\base\Action $action the action to be executed.
	 * @throws Exception if directory specified in migrationPath doesn't exist and action isn't "create".
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			$version = Yii::getVersion();
			$this->stdout("Yii PubSub Tool (based on Yii v{$version})\n\n");
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Creates a new producer/worker
	 *
	 * This command creates a new producer/worker using the available producer/worker template.
	 * After using this command, developers should modify the created producer/worker
	 * skeleton by filling up the actual producer logic.
	 *
	 * @param string $name the name of the new producer/worker. This should only contain
	 * letters, digits and/or underscores.
	 * @param string $path the path for generating new producer/worker.
	 * This will be path alias (e.g. "@app") default "@app"
	 * @throws Exception if the name argument is invalid.
	 */
	abstract public function actionCreate($name, $path = '@app');
	
	
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
	 * @param string $name the Producer / Worker class name
	 * @param string $type create Type 
	 * @param string $path The file path
	 */
	protected function createPubsub($name, $type, $path)
	{
		$namespace = $path;
		$path = Yii::getAlias($path);
		if (!is_dir($path)) {
			FileHelper::createDirectory($path);
		}
		
		$namespace = Yii::getAlias(str_replace('@','',str_replace('/','\\' , $namespace)));
		
		if(strpos($namespace, 'app') == 0 &&  strpos($namespace,'app') !== false){
			$_namespace = explode('\\', Yii::$app->controllerNamespace);
			array_pop($_namespace);
			$namespace = str_replace('app', implode('\\', $_namespace), $namespace);
		}
		
		$this->stdout("With Namespace:$namespace\n", Console::FG_GREEN);
		
		if (!preg_match('/^\w+$/', $name)) {
			throw new Exception('The '.$type.' name should contain letters, digits and/or underscore characters only.');
		}
		
		$className = 'm' . gmdate('ymd_His') . '_' . $name;
		$file = $path . DIRECTORY_SEPARATOR . $className . '.php';
		
		if ($this->confirm("Create new $type '$file'?")) {
			
			$content = $this->renderFile(Yii::getAlias($this->templateFile), ['className' => $className,'namespace'=>$namespace]);
		
			file_put_contents($file, $content);
			$this->stdout("New $type created successfully.\n", Console::FG_GREEN);
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
		return $this->checkPubsub($worker, 'No worker found');
	}
	
	/**
	 * Get all producer class name
	 */
	protected function getProducer(){
		$producer = [];
		foreach($this->producerPath as $path_alias){
			$producer = array_merge($producer,$this->readPubSub($path_alias));
		}
		sort($producer);
		return $this->checkPubsub($producer, 'No producer found');
	}
	
	/**
	 * Check Pubsub
	 * @param array $array pubsub class array
	 * @param string $message error message
	 * @return mixed
	 */
	protected function checkPubsub($array, $message){
		if (empty($array)) {
			$this->stdout("$message.\n", Console::FG_GREEN);
			return self::EXIT_CODE_NORMAL;
		}else{
			foreach ($array as $className) {
				$this->stdout("\t$className\n");
			}
			$this->stdout("\n");
			return 0;
		}
	}
}
