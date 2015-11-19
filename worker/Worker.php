<?php
/**
 * 
 */
namespace mithun\queue\worker;

use Yii;
use yii\base\Object;

/**
 * Worker Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
abstract  class Worker extends Object
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
	 * Runnable worker method
	 * @param array $params
	 */
	abstract public function run($params);
}