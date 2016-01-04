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
 * Producer Object
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class <?= $className ?> extends Producer
{	
	/**
	 * @inheritdoc
	 */
	public function run(){
		
	}
}
