<?php
/**
 * 
 */
namespace mithun\queue\producer;

use Yii;
use yii\base\Object;

/**
 * Producer Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
abstract class Producer extends Object
{	
	/**
	 * Min process to run
	 * @var integer
	 */
	public $min = 1;
	
	/**
	 * Max process to run
	 * @var integer
	 */
	public $max = 1;
	
	/**
	 * Runnable producer method
	 * @param array $params
	 */
	abstract public function run($params);
}