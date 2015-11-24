<?php
echo "<?php\n";
?>
/**
 * 
 */
namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use mithun\queue\worker\Worker;

/**
 * Worker Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class <?= $controllerClass ?> extends Worker
{
    /**
	 * @inheritdoc
	 */
	public $min = 1;
	
	/**
	 * @inheritdoc
	 */
	public $max = 1;
	
	/**
	 * @inheritdoc
	 */
	public function run($params){
		
	}
}
