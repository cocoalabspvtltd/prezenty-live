<?php


namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use backend\models\Event;
use backend\models\EventParticipant;
use api\components\Auth;

class GoLiveController extends ActiveController
{
    public $modelClass = 'backend\models\BlockedUser';

    public function beforeAction($action)
    {
       $auth = Auth::validateToken();
       if(count($auth) > 0) {
         $this->asJson($auth);
         return false;
       }

       return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            // For cross-domain AJAX request
            'corsFilter'  => [
                'class' => \yii\filters\Cors::className(),
                'cors'  => [
                    // restrict access to domains:
                    'Origin'                           => ["*"],
                    'Access-Control-Request-Method'    => ["POST"],
                    'Access-Control-Allow-Credentials' => true,
                    // 'Access-Control-Max-Age'           => 3600,                 // Cache (seconds)
                    'Access-Control-Request-Headers'           => ["*"],                 // Cache (seconds)
                ],
            ],

        ]);
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

    public function actionStart()
    {
      $event_id = Yii::$app->request->post('event_id');
      $event = Event::findOne($event_id);

      $participants = $event->participants;

      foreach($participants as $participant) {
        $payload = [
            'id' => $participant->id,
            'sender_email' => $participant->email,
            'date' => $event->date,
            'time' => $event->time,
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'event_title' => $event->title
        ];

        if($participant->email) {
          $message = $event->title . " event is streaming live. Tap to join";

          Yii::$app->notification->sendToOneUser(
            $message,
            $participant->email,
            'go_live',
            $message,
            false,
            $payload
          );
        }
      }

      
      return [
        'message' => "Notification sent successfully",
        'statusCode' => 200,
        'success' => "Success"
      ];
    }
    
    public function actionNoti(){
        
        $email='maneshmohan01@gmail.com';
        if($email) {
          $message =" event is streaming live. Tap to join";
         
          $payload=array();
          Yii::$app->notification->sendToOneUser(
            $message,
            $email,
            'go_live',
            $message,
            false,
            $payload
          );
        }
        
    }
}
