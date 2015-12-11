<?php
/**
 * 
 */
namespace mithun\queue\pubsub;

use Yii;
use yii\base\Object;
use yii\console\Controller;
use yii\helpers\FileHelper;
use Arara\Process\Action\Action;
use Arara\Process\Process;
use mithun\process\components\Process as Cprocess;
use mithun\process\components\ProcessPool;

/**
 * BasePubsub Object Base producer / worker class which has common methods
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
abstract class BasePubsub extends Object
{
	/**
	 * minimum process to run
	 * @var integer
	 */
	public $min = 1;
	
	/**
	 * maximum process to run
	 * @var integer
	 */
	public $max = 1;
	
	/**
	 * current controller
	 * @var Controller
	 */
	public $controller;
	
	public $pidfilePath = '@app/runtime/process';
	
	protected $process;
	protected $processPool;
	protected $pidfile;
	
	/**
	 * Execute Producer / Worker
	 */
	public function execute(){
		if($this->max > 1){
			$this->runProcessPool();			
		}else{
			$this->runProcess();
		}
	}
	
	/**
	 * Runnable worker method
	 */
	abstract public function run();
	
	/**
	 * Get Action
	 * @return Action
	 */
	abstract protected function getAction();
	
	/**
	 * Running ProcessPool
	 */
	protected function runProcessPool(){
		$this->processPool = Yii::createObject('mithun\process\components\ProcessPool');
		$this->processPool->createControl();
		$this->createPidfile();
		$this->processPool->create($this->max);
		$this->processPool->start();
		
		while(true){
			$this->attachClild();
			sleep(2);
		}
	}
	
	/**
	 * Attach a child process
	 */
	protected function attachClild(){
		$process = Yii::createObject('mithun\process\components\Process');
		$process->create($this->getAction(), 0, $this->processPool);
		$this->processPool->attach($process);
	}
	
	/**
	 * Running Process
	 */
	protected function runProcess(){
		$this->process = Yii::createObject('mithun\process\components\Process');
		$this->process->create($this->getAction());
		$this->process->start();
	}
	
	/**
	 * Create Pid file for Process
	 */
	protected function createPidfile(){
		$path = Yii::getAlias($this->pidfilePath);
		if (!is_dir($path)) {
			FileHelper::createDirectory($path);
		}
		
		$appName = (new \ReflectionClass($this))->getShortName();
		
		$appName = substr(str_replace('_', '', $appName), 0, 16); 
		
		if($this->processPool instanceof ProcessPool){
			$this->pidfile = $this->processPool->createPidfile($appName, $path);
		}elseif ($this->process instanceof Process){
			$this->pidfile = $this->process->createPidfile($appName, $path);
		}
		
		register_shutdown_function(array($this,'shutdownFn'));
	}
	
	public function shutdownFn(){
		$this->pidfile->finalize();
	}
}