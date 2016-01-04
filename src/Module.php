<?php

namespace mithun\queue;

use Yii;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'mithun\queue\controllers';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
