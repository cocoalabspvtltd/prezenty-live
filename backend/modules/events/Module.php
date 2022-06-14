<?php

namespace backend\modules\events;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * amctc module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\events\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $role = (Yii::$app->user->isGuest) ? '' : Yii::$app->user->identity->role;
        if($role != 'super-admin'){
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        // custom initialization code goes here
    }
}
