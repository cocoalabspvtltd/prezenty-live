<?php
namespace api\modules\v1;
use yii\filters\auth\HttpBasicAuth;

class Module extends \yii\base\Module
{

    public $controllerNamespace = 'api\modules\v1\controllers';
    public function init()
    {
        // print_r("enbtered");exit;
        parent::init();
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;

        // ...  other initialization code ...
    }
}
