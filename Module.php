<?php

namespace backend\modules\sensex;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;

class Module extends BaseModule implements BootstrapInterface
{
    public $controllerNamespace = 'backend\modules\sensex\controllers';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
    
    public function bootstrap($app)
    {
    	if ($app instanceof \yii\console\Application) {
    		$this->controllerNamespace = 'backend\modules\sensex\commands';
    	}
    }
}
