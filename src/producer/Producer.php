<?php
/**
 * 
 */
namespace mithun\queue\producer;

use Yii;
use mithun\queue\pubsub\BasePubsub;
use mithun\queue\pubsub\pubsubGetActionTrait; 

/**
 * Producer Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
abstract class Producer extends BasePubsub
{	
	use pubsubGetActionTrait;
	
	/**
	 * @inheritdoc
	 */
	public function init(){
		parent::init();
		$this->max = 1;
		$this->min = 1;
	}
}