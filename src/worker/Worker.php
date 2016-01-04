<?php
/**
 * 
 */
namespace mithun\queue\worker;

use Yii;
use mithun\queue\pubsub\BasePubsub;
use mithun\queue\pubsub\pubsubGetActionTrait;

/**
 * Worker Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
abstract class Worker extends BasePubsub
{	
	use pubsubGetActionTrait;
}