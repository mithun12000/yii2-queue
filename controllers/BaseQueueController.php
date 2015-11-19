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
	protected  $workerPath = ['@app/worker'];
	
	/**
	 * @var array the directory storing the producer classes. This can be either
	 * a path alias or a directory.
	 */
	protected  $producerPath = ['@app/producer'];
	
	/**
	 * @var string the template file for generating new worker.
	 * This can be either a path alias (e.g. "@app/worker/template.php")
	 * or a file path.
	 */
	public $workertemplateFile;
	
	/**
	 * @var string the template file for generating new producer.
	 * This can be either a path alias (e.g. "@app/producer/template.php")
	 * or a file path.
	 */
	public $producertemplateFile;
	
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
	 * Creates a new migration.
	 *
	 * This command creates a new migration using the available migration template.
	 * After using this command, developers should modify the created migration
	 * skeleton by filling up the actual migration logic.
	 *
	 * ~~~
	 * yii migrate/create create_user_table
	 * ~~~
	 *
	 * @param string $name the name of the new migration. This should only contain
	 * letters, digits and/or underscores.
	 * @throws Exception if the name argument is invalid.
	 */
	abstract public function actionCreate($name);
	
	
	/**
	 * Upgrades with the specified migration class.
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	protected function runWorker($class)
	{
		
	}
	
	/**
	 * Upgrades with the specified migration class.
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	protected function runProducer($class)
	{
	
	}
	
	/**
	 * Creates a new migration instance.
	 * @param string $class the migration class name
	 * @return \yii\db\MigrationInterface the migration instance
	 */
	protected function createMigration($class)
	{
		$file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
		require_once($file);
		return new $class();
	}
}
