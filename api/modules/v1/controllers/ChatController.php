<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
use backend\models\Chat;
use api\modules\v1\models\Account;
use backend\models\Event;
use backend\models\BlockedUser;
use backend\models\Music;
use backend\models\EventGiftVoucher;
use backend\models\EventMenuGift;
use backend\models\EventParticipant;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class ChatController extends ActiveController
{
    public $modelClass = 'backend\models\Chat';
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

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }    

    public function actionSendMessage(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $message = isset($post['message'])?$post['message']:'';
        $sender_email = isset($post['sender_email'])?$post['sender_email']:'';
        $receiver_email = isset($post['receiver_email'])?$post['receiver_email']:'';
        $time = isset($post['time'])?$post['time']:'';
        $date = isset($post['date'])?$post['date']:'';
        if($event_id && $sender_email && $date && $time){
            $modelBlockedUser = BlockedUser::find()->where(['event_id' => $event_id,'blocked_user_email' => $receiver_email, 'blocked_by_user_email' => $sender_email,'status'=>1])->one();
            if($modelBlockedUser){
                $msg = "This user is blocked by you";
                $ret = [
                    "message" => $msg,
                    "statusCode" => 200,
                    "success" => $success          
                ];
                return $ret;
            }
            $modelBlockedUser = BlockedUser::find()->where(['event_id' => $event_id,'blocked_user_email' => $sender_email, 'blocked_by_user_email' => $receiver_email,'status'=>1])->one();
            if($modelBlockedUser){
                $msg = "You are blocked by ".$receiver_email;
                $ret = [
                    "message" => $msg,
                    "statusCode" => 200,
                    "success" => $success          
                ];
                return $ret;
            }
            $model = new Chat;
            $model->event_id = $event_id;
            $model->sender_email = $sender_email;
            $model->date = date('Y-m-d',strtotime($date));
            $model->time = $time;
            $model->message = $message;
            $isGroupMessage = true;
            if($receiver_email){
                $isGroupMessage = false;
                $model->receiver_email = $receiver_email;
            }
            $model->save(false);
            $title = "New message";
            $type = 'message';
            $typeVal = $message;
            $dataArr = [
                'id' => $model->id,
                'event_id' => $event_id,
                'sender_email' => $sender_email,
                'date' => $date,
                'event_title' => '',
                'time' => $time
            ];
            if($receiver_email){
                $value = $receiver_email;
                $notification = Yii::$app->notification->sendToOneUser($title,$value,$type,$typeVal,$isGroupMessage,$dataArr);
            }else{
                $modelEventParticipant = EventParticipant::find()->where(['event_id'=>$event_id,'status'=>1])->all();
                // print_r($notification);exit;
                foreach($modelEventParticipant as $eventParticipant){
                    $value = $eventParticipant->email;
                    if($sender_email != $value){
                        $notification = Yii::$app->notification->sendToOneUser($title,$value,$type,$typeVal,$isGroupMessage,$dataArr);
                    }
                    // print_r($notification);
                }
                $modelEvent = Event::find()->where(['id'=>$event_id])->one();
                $modelEventCreator = User::find()->where(['id'=>$modelEvent->user_id])->one();
                $value = $modelEventCreator->email;
                if($sender_email != $value){
                    $notification = Yii::$app->notification->sendToOneUser($title,$value,$type,$typeVal,$isGroupMessage,$dataArr);
                }
            }
            $msg = "Message sent successfully";
            $success = true;
        }else{
            $msg = "Message and Event ID and Sender Email and Date and Time cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success          
        ];
        return $ret;
    }
    public function actionGroupMessage(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $ret = [];
        $msg = '';
        $success = false;
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        if(!$event_id){
            $msg = "Event id cannot be blank";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $query = Chat::find()->where(['status'=>1])->andWhere(['event_id'=>$event_id])->andWhere(['receiver_email' => null]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $ret = [
            'statusCode' => 200,
            'list' => $dataProvider,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
    public function actionMessage(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $ret = [];
        $msg = '';
        $success = false;
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        $receiver_email = isset($get['receiver_email'])?$get['receiver_email']:'';
        $sender_email = isset($get['sender_email'])?$get['sender_email']:'';
        if(!$event_id && !$sender_email && !$receiver_email){
            $msg = "Event id and sender email and receiver email cannot be blank";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $query = Chat::find()->where(['status'=>1])->andWhere(['event_id'=>$event_id])
        ->andWhere(['or',['receiver_email'=>$receiver_email,'sender_email'=>$sender_email],['sender_email'=>$receiver_email,'receiver_email'=>$sender_email]]);
        // ->andWhere(['or',['sender_email'=>$receiver_email,'receiver_email'=>$receiver_email]]);
        // ->andWhere(['or',[['or',['receiver_email'=>$receiver_email,'sender_email'=>$receiver_email]],['receiver_email'=>$sender_email,'sender_email'=>$sender_email]]]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $ret = [
            'statusCode' => 200,
            'list' => $dataProvider,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
    public function actionBlockUser(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $blocked_user_email = isset($post['blocked_user_email'])?$post['blocked_user_email']:'';
        $blocked_by_user_email = isset($post['blocked_by_user_email'])?$post['blocked_by_user_email']:'';
        if($event_id && $blocked_user_email && $blocked_by_user_email){
            $model = BlockedUser::find()->where(['event_id' => $event_id,'blocked_user_email' => $blocked_user_email, 'blocked_by_user_email' => $blocked_by_user_email,'status'=>1])->one();
            if(!$model){
                $model = new BlockedUser;
            }
            $model->event_id = $event_id;
            $model->blocked_user_email = $blocked_user_email;
            $model->blocked_by_user_email = $blocked_by_user_email;
            $model->status = 1;
            $model->save(false);
            $msg = "User Blocked successfully";
            $success = true;
        }else{
            $msg = "Event id and blocked user email and blocked by user email cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success          
        ];
        return $ret;
    }
    public function actionUnBlockUser(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $blocked_user_email = isset($post['blocked_user_email'])?$post['blocked_user_email']:'';
        $blocked_by_user_email = isset($post['blocked_by_user_email'])?$post['blocked_by_user_email']:'';
        if($event_id && $blocked_user_email && $blocked_by_user_email){
            $model = BlockedUser::find()->where(['event_id' => $event_id,'blocked_user_email' => $blocked_user_email, 'blocked_by_user_email' => $blocked_by_user_email,'status'=>1])->one();
            if($model){
                $model->status = 0;
                $model->save(false);
                $msg = "User Un Blocked successfully";
                $success = true;
            }else{
                $msg = "Invalid Information";
            }
        }else{
            $msg = "Event id and blocked user email and blocked by user email cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success          
        ];
        return $ret;
    }
}