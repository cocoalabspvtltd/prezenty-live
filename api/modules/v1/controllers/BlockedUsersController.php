<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use backend\models\BlockedUser;
use api\components\Auth;

class BlockedUsersController extends ActiveController
{
    public $modelClass = 'backend\models\BlockedUser';
 public function behaviors()
    {

        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);


        $behaviors['corsFilter'] = [

            'class' => \yii\filters\Cors::className(),

            'cors' => [

                'Origin' => ['*'],

                'Access-Control-Allow-Origin' => ['*'],

                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],

                'Access-Control-Request-Headers' => ['*'],

                'Access-Control-Allow-Credentials' => null,

                'Access-Control-Max-Age' => 86400,

                'Access-Control-Expose-Headers' => []

            ]

        ];

        return $behaviors;

    }



    public function beforeAction($action)
    {
       /*$auth = Auth::validateToken();
       if(count($auth) > 0) {
         $this->asJson($auth);
         return false;
       }*/

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

    public function actionIndex($event_id, $blocked_by_user_email)
    {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Headers: *');

      return [
        'list' => BlockedUser::find()
              ->with(['participant' =>  function (\yii\db\ActiveQuery $query) use($event_id) {
                  $query->andOnCondition(['event_id' => $event_id]);
              }])
              ->where(['blocked_user.event_id' => $event_id])
			  ->andWhere(['blocked_by_user_email' => $blocked_by_user_email])
			  ->andWhere(['status' => 1])
              ->asArray()
              ->all(),
        'message' => 'Success',
        'statusCode' => 200,
        'success' => true
      ];
    }
}