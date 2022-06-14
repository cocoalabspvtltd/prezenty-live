<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use backend\models\EventParticipant;

class ParticipantsController extends ActiveController
{
    public $modelClass = 'backend\models\EventParticipant';

    public function beforeAction($action)
    {
       $auth = Auth::validateToken();
       if(count($auth) > 0) {
         $this->asJson($auth);
         return false;
       }

       return parent::beforeAction($action);
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);

        return $actions;
    }

    public function actionIndex($event_id)
    {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Headers: *');

      return [
        'list' => EventParticipant::find()
              ->joinWith(['order'])
              ->where([
                'event_participant.event_id' => $event_id,
                'need_food' => 1,
              ])
              ->andWhere(['or', 
                ['payment_status' => 0],
                ['payment_status' => NULL]
              ])
              ->asArray()
              ->all(),
        'message' => 'Success',
        'success' => true,
        'statusCode' => 200,
      ];
    }
}
