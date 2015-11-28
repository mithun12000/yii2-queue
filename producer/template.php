<?php
echo "<?php\n";
?>
/**
 * 
 */
namespace <?= $namespace ?>;

use Yii;
use mithun\queue\producer\Producer;

/**
 * Worker Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class <?= $className ?> extends Worker
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
