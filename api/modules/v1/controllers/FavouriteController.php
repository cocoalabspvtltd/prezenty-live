<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Event;
use backend\models\Point;
use backend\models\Favourite;
// use backend\models\Log;
use api\modules\v1\models\Account;
use backend\models\Music;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class FavouriteController extends ActiveController
{
    public $modelClass = 'backend\models\Favourite';    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }    
    public function  actionList(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $get = Yii::$app->request->get();
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $token = (new Account)->getBearerToken($api_token);
        $validateToken = (new Account)->validateToken($token);
        if(!$validateToken){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $user_details = (new Account)->getCusomerDetailsByAPI($token); 
        if(!$user_details){
            Yii::$app->response->statusCode = 401;
            $msg = "Somthing went wrong.";
            $ret = [
                'message' => $msg,
                'statusCode' => 401,
                'success' => $success
            ];
            return $ret;
        }
        $query = Favourite::find()
        ->where(['status' => 1])
        ->andWhere(['user_id' => $user_details->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $list = [];
        foreach($dataProvider->getModels() as $notification){
            $eventDetail = Event::find()->where(['status'=>1,'id'=>$notification->event_id])->one();
            $notificationList = [
                'id' => $notification->id,
                'event_id' => $notification->event_id,
                'user_id' => $notification->user_id,
                'status' => $notification->status,
                'created_at' => $notification->created_at,
                'modified_at' => $notification->modified_at,
                'eventDetail' => $eventDetail
            ];
            $list[] = $eventDetail;
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $ret = [
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'baseUrlVideo' => Yii::$app->params['base_path_event_video'],
            'statusCode' => 200,
            'list' => $list,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
    public function actionAdd(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = '';
        $statusCode = 200;
        $success = false;
        $post = Yii::$app->request->post();
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $token = (new Account)->getBearerToken($api_token);
        $validateToken = (new Account)->validateToken($token);
        if(!$validateToken){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $user_details = (new Account)->getCusomerDetailsByAPI($token); 
        if(!$user_details){
            Yii::$app->response->statusCode = 401;
            $msg = "Somthing went wrong.";
            $ret = [
                'message' => $msg,
                'statusCode' => 401,
                'success' => $success
            ];
            return $ret;
        }
        if($event_id){
            $model = Favourite::find()->where(['status'=>1,'event_id'=>$event_id,'user_id'=>$user_details->id])->one();
            if($model){
                $model->status = 0;
                $model->save(false);
                $msg = "Removed from favourites";
                $success = true;
            }else{
                $model = new Favourite;
                $model->user_id = $user_details->id;
                $model->event_id = $event_id;
                $model->save(false);
                $msg = "Added to favourites";
                $success = true;
            }
        }else{
            $msg = "Event id cannot be blank";
        }
        $ret = [
            'message' => $msg,
            'statusCode' => $statusCode,
            'success' => $success
        ];
        return $ret;
    }
}