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
 * Producer Controller
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class ProducerController extends BaseQueueController
{
	/**
	 * @var string the default command action.
	 */
	public $defaultAction = 'producer';
	
	/**
	 * Upgrades the application by applying new migrations.
	 * For example,
	 *
	 * ~~~
	 * yii migrate     # apply all new migrations
	 * yii migrate 3   # apply the first 3 new migrations
	 * ~~~
	 *
	 * @param integer $limit the number of new migrations to be applied. If 0, it means
	 * applying all available new migrations.
	 *
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 */
	public function actionProducer($producer = '')
	{
		$migrations = $this->getNewMigrations();
		if (empty($migrations)) {
			$this->stdout("No new migration found. Your system is up-to-date.\n", Console::FG_GREEN);
			return self::EXIT_CODE_NORMAL;
		}
		$total = count($migrations);
		$limit = (int) $limit;
		if ($limit > 0) {
			$migrations = array_slice($migrations, 0, $limit);
		}
		$n = count($migrations);
		if ($n === $total) {
			$this->stdout("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
		} else {
			$this->stdout("Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
		}
		foreach ($migrations as $migration) {
			$this->stdout("\t$migration\n");
		}
		$this->stdout("\n");
		$applied = 0;
		if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
			foreach ($migrations as $migration) {
				if (!$this->migrateUp($migration)) {
					$this->stdout("\n$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were') ." applied.\n", Console::FG_RED);
					$this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);
					return self::EXIT_CODE_ERROR;
				}
				$applied++;
			}
			$this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') ." applied.\n", Console::FG_GREEN);
			$this->stdout("\nMigrated up successfully.\n", Console::FG_GREEN);
		}
	}
	
	/**
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