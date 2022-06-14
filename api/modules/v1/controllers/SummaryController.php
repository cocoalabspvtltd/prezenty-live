<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
use backend\models\Favourite;
use backend\models\EventGiftVoucherReceived;
use backend\models\Event;
use backend\models\MenuGift;
use backend\models\MenuGiftItems;
use backend\models\EventParticipant;
use backend\models\EventMenuGift;
use backend\models\Order;
use backend\models\GiftVoucher;
use backend\models\MenuOrderPayment;
use backend\models\EventGiftVoucher;
use backend\models\GiftVoucherRedeem;
use backend\models\GiftVoucherTransactions;
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
class SummaryController extends ActiveController
{
    public $modelClass = 'backend\models\EventGiftVoucherReceived';    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }    

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

    public function actionReports(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $list = null;
        
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
        $get = Yii::$app->request->get();
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        if(!$event_id){
            $msg = "Event Id cannot be blank";
        }else{
            $qry = GiftVoucher::find()->leftJoin('event_gift_voucher','event_gift_voucher.gift_voucher_id=gift_voucher.id')
            ->where(['event_gift_voucher.status' => 1])
            ->andWhere(['event_gift_voucher.event_id' => $event_id]);
            $dataProvider = new ActiveDataProvider([
                'query' => $qry,
                'pagination' => false
            ]);
            if($dataProvider->getModels()){
                foreach($dataProvider->getModels() as $model){
                    $modelEventGiftVoucherReceived = EventGiftVoucherReceived::find()->where(['status'=>1,'event_id'=>$event_id,'event_gift_id'=>$model->id]);
                    $list[] = [
                        'title' => $model->title,
                        'count' => (int) $modelEventGiftVoucherReceived->count(),
                        'price' => (int) $modelEventGiftVoucherReceived->sum('amount'),
                        'color' => $model->color_code
                    ];
                }
            }
        }
        $success = true;
        $ret = [
            'message' => $msg,
            'statusCode' => $statusCode,
            'success' => $success,
            'list' => $list
        ];
        return $ret;
    }
    public function actionSendNotification(){
        $post = Yii::$app->request->post();
        $message = isset($post['message'])?$post['message']:'';
        $value = isset($post['value'])?$post['value']:'';
        if($message && $value){
            $title = "New message";
            $type = 'message';
            $typeVal = $message;
            $notification = Yii::$app->notification->sendToOneUser($title,$value,$type,$typeVal);
            $ret = [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $notification
            ];
            return $ret;
        }else{
            $ret = [
                'success' => false,
                'message' => 'Message cannot be blank'
            ];
            return $ret;
        }
    }
    public function actionGiftVouchers(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $list = null;
        $eventDetail = null;
        
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
            ];
            return $ret;
        }
        $get = Yii::$app->request->get();
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        $modelEvent = Event::find()->where(['status'=>1,'id'=>$event_id,'user_id'=>$user_details->id])->one();
        if($modelEvent){
            $eventDetail = $modelEvent;
            $models = EventGiftVoucherReceived::find()->where(['status'=>1,'event_id'=>$event_id])->all();
            foreach($models as $model){
                $modelGiftVoucher = GiftVoucher::find()->where(['status'=>1,'id'=>$model->event_gift_id])->one();
                $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$model->event_participant_id])->one();
                $gift = [
                    'id' => $model->id,
                    'participantName' => ($modelEventParticipant->name)?$modelEventParticipant->name:'',
                    'giftVoucherImageUrl' => ($modelGiftVoucher->image_url)?$modelGiftVoucher->image_url:'',
                    'giftVoucherDetail' => ($modelGiftVoucher->title)?$modelGiftVoucher->title:'',
                    'date' => ($model->created_at)?date('d M Y',strtotime($model->created_at)):'',
                    'time' => ($model->created_at)?date('h:i a',strtotime($model->created_at)):'',
                ];
                $list[] = $gift;
            }
        }else{
            $msg = "Invalid Event Id";
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $baseUrl = Yii::$app->params['base_path_voucher_images'];
        $ret = [
            'success' => true,
            'message' => $msg,
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'giftVoucherBaseUrl' => $baseUrl,
            'statusCode' => $statusCode,
            'list' => $list,
            'eventDetail' => $eventDetail
        ];
        return $ret;
    }
    
    public function actionGiftVouchersReceived(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $list = null;
        $modelEvent = null;
        $models = [];
        
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
            ];
            return $ret;
        }
        $get = Yii::$app->request->get();
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        $modelEvent = Event::find()->where(['status'=>1,'id'=>$event_id,'user_id'=>$user_details->id])->one();
        if($modelEvent){
            $models = GiftVoucher::find()
            ->leftJoin('event_gift_voucher_received','event_gift_voucher_received.event_gift_id=gift_voucher.id')
            ->where(['event_gift_voucher_received.status'=>1,'event_id'=>$event_id,'gift_voucher.status'=>1])
            ->groupBy('event_gift_id')
            ->all();
        }else{
            $msg = "Invalid Event Id";
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $baseUrl = Yii::$app->params['base_path_voucher_images'];
        $ret = [
            'success' => true,
            'message' => $msg,
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'giftVoucherBaseUrl' => $baseUrl,
            'statusCode' => $statusCode,
            'list' => $models,
            'eventDetail' => $modelEvent
        ];
        return $ret;
    }
    public function actionGiftVoucherUsersList(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $list = null;
        $modelEvent = null;
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
            ];
            return $ret;
        }
        $get = Yii::$app->request->get();
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        $gift_voucher_id = isset($get['gift_voucher_id'])?$get['gift_voucher_id']:'';
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $totalCount = 0;
        $hasNextPage = false;
        if(!$event_id || !$gift_voucher_id){
            $msg = "Event id and gift voucher id cannot be blank.";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'list' => $list
            ];
            return $ret;
        }
        $modelEvent = Event::find()->where(['status'=>1,'id'=>$event_id,'user_id'=>$user_details->id])->one();
        if($modelEvent){
            $query = EventGiftVoucherReceived::find()
            ->leftJoin('gift_voucher','gift_voucher.id=event_gift_voucher_received.event_gift_id')
            ->where(['event_gift_voucher_received.status'=>1,'event_id'=>$event_id,'gift_voucher.status'=>1,'gift_voucher.id'=>$gift_voucher_id]);
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
            $totalCount = (int) $query->count();
            foreach($dataProvider->getModels() as $model){
                $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$model->event_participant_id])->one();
                $gift = [
                    'id' => $model->id,
                    'participantName' => ($modelEventParticipant->name)?$modelEventParticipant->name:'',
                    'amount' => $model->amount,
                    'date' => $model->created_at
                ];
                $list[] = $gift;
            }
        }else{
            $msg = "Invalid Event Id";
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $baseUrl = Yii::$app->params['base_path_voucher_images'];
        $ret = [
            'success' => true,
            'message' => $msg,
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'giftVoucherBaseUrl' => $baseUrl,
            'statusCode' => $statusCode,
            'list' => $list,
            'page' => $page,
            'per_page' => $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => $totalCount,
            'eventDetail' => $modelEvent
        ];
        return $ret;
    }
    public function actionMenuOrders(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $list = null;
        
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
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
                'success' => $success,
                'list' => $list
            ];
            return $ret;
        }
        $get = Yii::$app->request->get();
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $is_veg = isset($get['is_veg'])?$get['is_veg']:'';
        $is_non_veg = isset($get['is_non_veg'])?$get['is_non_veg']:'';
        $is_gift = isset($get['is_gift'])?$get['is_gift']:'';
        $modelEvent = Event::find()->where(['status'=>1,'id'=>$event_id,'user_id'=>$user_details->id])->one();
        if($modelEvent){
            $query = EventParticipant::find()->where(['status'=>1,'event_id'=>$event_id,'is_attending'=>0])->andWhere(['or',['need_food'=>1],['need_gift'=>1]]);
            if($is_veg){
                $query->andWhere(['is_veg'=>1,'need_gift'=>0]);
            }
            if($is_non_veg){
                $query->andWhere(['is_veg'=>0,'need_gift'=>0]);
            }
            if($is_gift){
                $query->andWhere(['need_gift'=>$is_gift]);
            }
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
            foreach($dataProvider->getModels() as $model){
                $modelMenuOrderPayment = MenuOrderPayment::find()->where(['status'=>1,'event_id'=>$model->event_id,'participant_id'=>$model->id,'is_paid'=>1])->one();
                $isPaid = false;
                if($modelMenuOrderPayment){
                    $isPaid = true;
                }
                $list[] = [
                    "id" =>  $model->id,
                    "event_id" =>  $model->event_id,
                    "status" =>  $model->status,
                    "created_at" =>  $model->created_at,
                    "modified_at" =>  $model->modified_at,
                    "name" =>  $model->name,
                    "phone" =>  $model->phone,
                    "email" =>  $model->email,
                    "address" =>  $model->address,
                    "members_count" =>  $model->members_count,
                    "is_veg" =>  $model->is_veg,
                    "need_food" =>  $model->need_food,
                    "need_gift" =>  $model->need_gift,
                    "is_attending" =>  $model->is_attending,
                    "is_paid" => $isPaid
                ];
            }
            $ret = [
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
        }else{
                $msg = "Invalid Event Id";
        }
        $ret = [
            'success' => $success,
            'message' => $msg,
            'statusCode' => $statusCode,
            'list' => $list
        ];
        return $ret;
    }
    public function actionGiftVoucherDetail(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $result = null;
        $eventDetail = null;
        
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'result' => $result
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
                'success' => $success,
                'result' => $result
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
                'success' => $success,
                'result' => $result
            ];
            return $ret;
        }
        $get = Yii::$app->request->get();
        $gift_id = isset($get['gift_id'])?$get['gift_id']:'';
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        if(!$event_id || !$gift_id){
            $msg = "Event id and gift id cannot be blank.";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'result' => $result
            ];
            return $ret;
        }
        $model = Event::find()->where(['status'=>1,'id'=>$event_id])->one();
        $modelEventGiftVoucher = EventGiftVoucher::find()->where(['status'=>1,'event_id'=>$event_id,'gift_voucher_id'=>$gift_id])->one();
        if(!$modelEventGiftVoucher){
            $msg = "Invalid event Id";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'result' => $result
            ];
            return $ret;
        }
        $modelGiftVoucher = GiftVoucher::find()->where(['status'=>1,'id'=>$modelEventGiftVoucher->gift_voucher_id])->one();
        if(!$model || !$modelEventGiftVoucher || !$modelGiftVoucher){
            $msg = "Invalid event Id or gift Id";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success,
                'result' => $result
            ];
            return $ret;
        }
        $isActive = false;
        $modelGiftVoucherTransactions = GiftVoucherTransactions::find()->where(['status'=>1,'event_id'=>$event_id,'vendor_id'=>$modelEventGiftVoucher->gift_voucher_id])->one();
        if($modelGiftVoucherTransactions){
            $isActive = true;
        }
        $eventDetail = $model;
        $totalAmount = EventGiftVoucherReceived::find()->where(['status'=>1,'event_id'=>$event_id,'event_gift_id'=>$gift_id])->sum('amount');
        $transactionHistoryQuery = GiftVoucherRedeem::find()->where(['status'=>1,'event_gift_voucher_id'=>$modelEventGiftVoucher->id]);
        $activeAmount = GiftVoucherTransactions::find()->where(['event_id'=>$event_id,'vendor_id'=>$modelEventGiftVoucher->gift_voucher_id])->sum('amount');
        $inActiveAmount = $totalAmount - $activeAmount;
        $availableAmount = $activeAmount - $transactionHistoryQuery->sum('amount');
        $result = [
            'id' => $modelGiftVoucher->id,
            'giftVoucherImageUrl' => ($modelGiftVoucher->image_url)?$modelGiftVoucher->image_url:'',
            'giftVoucherTitle' => ($modelGiftVoucher->title)?$modelGiftVoucher->title:'',
            'giftVoucherDescription' => ($modelGiftVoucher->description)?$modelGiftVoucher->description:'',
            'barcode' => ($modelEventGiftVoucher->barcode)?$modelEventGiftVoucher->barcode.'.png':'',
            'cardNumber' => ($modelEventGiftVoucher->barcode)?(int) $modelEventGiftVoucher->barcode:'', 
            'totalAmount' => (int) $totalAmount,
            'isActive' => $isActive,
            'availableAmount' => (int) $availableAmount,
            'activeAmount' => (int) $activeAmount,
            'inActiveAmount' => (int) $inActiveAmount
        ];
        $transactionHistory = $transactionHistoryQuery->all();
        $months = [
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];
        $transferList = [];
        foreach($transactionHistory as $history){
            foreach($months as $key => $value){
                $date = date('m',strtotime($history->date));
                if($date == $key){
                    $transferList[$value][] = $history;
                }
            }
        }
        $history = [];
        foreach($transferList as $month => $data){
            $history[] = [
                'key' => $month,
                'value' => $data
            ];
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $barcodeLocation = Yii::$app->params['base_path_barcode'];
        $baseUrl = Yii::$app->params['base_path_voucher_images'];
        $ret = [
            'success' => true,
            'message' => $msg,
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'barcodeLocation' => $barcodeLocation,
            'giftVoucherBaseUrl' => $baseUrl,
            'statusCode' => $statusCode,
            'result' => $result,
            'eventDetail' => $eventDetail,
            'transactionHistory' => $history
        ];
        return $ret;
    }
    
    public function actionMenuOrderPayment(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        
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
        $post = Yii::$app->request->get();
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $user_ids = isset($post['user_ids'])?$post['user_ids']:'';
        $totalAmount = 0;
        if($event_id && $user_ids){
            $modelMenuGift = MenuGift::find()
            ->leftJoin('event_menu_gift','event_menu_gift.menu_gift_id=menu_gift.id')
            ->where(['event_id'=>$event_id])->all();
            $giftCount = 0;
            $vegCount = 0;
            $nonVegCount = 0;
            $userIds = explode(',', $user_ids);
            foreach($userIds as $userId){
                $modelParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$userId,'event_id'=>$event_id])->one();
                if($modelParticipant){
                    if($modelParticipant->need_gift == 1){
                        $giftCount = $giftCount + $modelParticipant->members_count; 
                    }
                    if($modelParticipant->need_food == 1 && $modelParticipant->is_veg == 1){
                        $vegCount = $vegCount + $modelParticipant->members_count;
                    }
                    if($modelParticipant->need_food == 1 && $modelParticipant->is_veg == 0){
                        $nonVegCount = $nonVegCount + $modelParticipant->members_count;
                    }
                }
            }
            $giftArray = [];
            $vegArray = [];
            $nonVegArray = [];
            foreach($modelMenuGift as $menuGift){
                if($menuGift->is_gift == 1){
                    $menuGiftArray = [
                        "id" => $menuGift->id,
                        "title" => $menuGift->title,
                        "price" => $menuGift->price,
                        "rating" => $menuGift->rating,
                        "is_gift" => $menuGift->is_gift,
                        "is_veg" => $menuGift->is_veg,
                        "is_non_veg" => $menuGift->is_non_veg,
                        "image_url" => $menuGift->image_url,
                        "status" => $menuGift->status,
                        "created_at" => $menuGift->created_at,
                        "modified_at" => $menuGift->modified_at,
                        "count" => $giftCount
                    ];
                    $giftArray = $menuGiftArray;
                    $totalAmount = $totalAmount + ($giftCount * $menuGift->price);
                }
                if($menuGift->is_gift == 0 && $menuGift->is_veg == 1 && $menuGift->is_non_veg == 0){
                    $itemList = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$menuGift->id])->all();
                    $menuGiftArray1 = [
                        "id" => $menuGift->id,
                        "title" => $menuGift->title,
                        "price" => $menuGift->price,
                        "rating" => $menuGift->rating,
                        "is_gift" => $menuGift->is_gift,
                        "is_veg" => $menuGift->is_veg,
                        "is_non_veg" => $menuGift->is_non_veg,
                        "image_url" => $menuGift->image_url,
                        "status" => $menuGift->status,
                        "created_at" => $menuGift->created_at,
                        "modified_at" => $menuGift->modified_at,
                        "count" => $vegCount,
                        "menuItems" => $itemList
                    ];
                    $vegArray = $menuGiftArray1;
                    $totalAmount = $totalAmount + ($vegCount * $menuGift->price);
                }
                if($menuGift->is_gift == 0 && $menuGift->is_veg == 0 && $menuGift->is_non_veg == 1){
                    $nonVegItemList = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$menuGift->id])->all();
                    $menuGiftArray2 = [
                        "id" => $menuGift->id,
                        "title" => $menuGift->title,
                        "price" => $menuGift->price,
                        "rating" => $menuGift->rating,
                        "is_gift" => $menuGift->is_gift,
                        "is_veg" => $menuGift->is_veg,
                        "is_non_veg" => $menuGift->is_non_veg,
                        "image_url" => $menuGift->image_url,
                        "status" => $menuGift->status,
                        "created_at" => $menuGift->created_at,
                        "modified_at" => $menuGift->modified_at,
                        "count" => $nonVegCount,
                        "menuItems" => $nonVegItemList
                    ];
                    $nonVegArray = $menuGiftArray2;
                    $totalAmount = $totalAmount + ($nonVegCount * $menuGift->price);
                }
            }
            $return = [];
            if($giftArray){
                $return[] = $giftArray;
            }
            if($vegArray){
                $return[] = $vegArray;
            }
            if($nonVegArray){
                $return[] = $nonVegArray;
            }
            $musicFilesLocation = Yii::$app->params['base_path_music_files'];
            $imageFilesLocation = Yii::$app->params['base_path_event_images'];
            $barcodeLocation = Yii::$app->params['base_path_barcode'];
            $baseUrl = Yii::$app->params['base_path_voucher_images'];
            $menuOrderBaseUrl = Yii::$app->params['base_path_menu_images'];
            $ret = [
                'baseUrl' => $baseUrl,
                'menuOrderBaseUrl' => $menuOrderBaseUrl,
                'message' => 'successfully listed',
                'success' => true,
                'statusCode' => 200,
                'result' => $return,
                'totalAmount' => $totalAmount,
                'event_id' => (int) $event_id,
                'participantIds' => $user_ids
            ];
            return $ret;
        }else{
            $msg = 'Event id and user ids cannot be blank';
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
    }
    public function actionOrders(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $modelEvent = [];
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
        $get = Yii::$app->request->get();
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $query = Order::find()
        ->leftJoin('event','event.id=order.event_id')
        ->where(['order.status'=>1])->andWhere(['user_id'=>$user_details->id]);
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
        $orders = [];
        foreach($dataProvider->getModels() as $order){
            $modelEvent = Event::find()->where(['status'=>1,'id'=>$order->event_id])->one();
            $orderList = [
                'eventDetail' => $modelEvent,
                'orderDetail' => $order
            ];
            $orders[] = $orderList;
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $ret = [
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'statusCode' => 200,
            'list' => $orders,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
    public function actionOrderDetail(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $modelEvent = [];
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
        $get = Yii::$app->request->get();
        $order_id = isset($get['order_id'])?$get['order_id']:'';
        if(!$order_id){
            $msg = "order id cannot be blank";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => false
            ];
            return $ret;
        }
        $modelOrder = Order::find()->where(['status'=>1,'id'=>$order_id])->one();
        if(!$modelOrder){
            $msg = 'Invalid order Id';
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => false
            ];
            return $ret;
        }
        $modelEvent = Event::find()->where(['status'=>1,'id'=>$modelOrder->event_id,'user_id'=>$user_details->id])->one();
        if(!$modelEvent){
            $msg = 'Invalid order Id';
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => false
            ];
            return $ret;
        }
        $giftCount = $modelOrder->gift_count;
        $vegCount = $modelOrder->veg_count;
        $nonvegCount = $modelOrder->non_veg_count;
        $giftDetail = MenuGift::find()->where(['status'=>1,'id'=>$modelOrder->menu_gift_id])->one();
        $vegDetail = MenuGift::find()->where(['status'=>1,'id'=>$modelOrder->menu_veg_id])->one();
        $vegList = [];
        $modelGiftItems = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$vegDetail->id])->all();
        $vegList = [
            'detail' => $vegDetail,
            'items' => $modelGiftItems
        ];
        $nonVegDetail = MenuGift::find()->where(['status'=>1,'id'=>$modelOrder->menu_non_veg_id])->one();
        $nonVegList = [];
        $modelGiftItemsNonVeg = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$nonVegDetail->id])->all();
        $nonVegList = [
            'detail' => $nonVegDetail,
            'items' => $modelGiftItemsNonVeg
        ];
        $eventImageFilesLocation = Yii::$app->params['base_path_event_images'];
        $giftVoucherImageLocation = Yii::$app->params['base_path_voucher_images'];
        $baseUrl = Yii::$app->params['base_path_menu_images'];
        $ret = [
            'eventImageFilesLocation' => $eventImageFilesLocation,
            'giftVoucherImageLocation' => $giftVoucherImageLocation,
            'baseUrl' => $baseUrl,
            'orderDetail' => $modelOrder,
            'eventDetail' => $modelEvent,
            'giftCount' => $giftCount,
            'giftDetail' => $giftDetail,
            'vegCount' => $vegCount,
            'vegDetail' => $vegList,
            'nonvegCount' => $nonvegCount,
            'nonVegDetail' => $nonVegList,
            'message' => 'Order detail listed successfully',
            'statusCode' => 200,
            'success' => true
        ];
        return $ret;
    }
    public function actionOrderDetailUsers(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $modelEvent = [];
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
        $get = Yii::$app->request->get();
        $order_id = isset($get['order_id'])?$get['order_id']:'';
        $gift_id = isset($get['gift_id'])?$get['gift_id']:'';
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        if(!$order_id && !$gift_id){
            $msg = "order id and gift id cannot be blank";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => false
            ];
            return $ret;
        }
        $modelOrder = Order::find()->where(['status'=>1,'id'=>$order_id])->one();
        if(!$modelOrder){
            $msg = 'Invalid order Id';
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => false
            ];
            return $ret;
        }
        $usersList = [];
        $modelMenuGift = MenuGift::find()->where(['id'=>$gift_id])->one();
        if($modelMenuGift->is_gift == 1){
            $query = EventParticipant::find()->where(['status'=>1,'need_gift'=>1,'event_id'=>$modelOrder->event_id]);
        }elseif($modelMenuGift->is_veg == 1){
            $query = EventParticipant::find()->where(['status'=>1,'need_food'=>1,'is_veg'=>1,'event_id'=>$modelOrder->event_id]);
        }else{
            $query = EventParticipant::find()->where(['status'=>1,'need_food'=>1,'is_veg'=>0,'event_id'=>$modelOrder->event_id]);
        }
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
        $ret = [
            'page' => $page,
            'per_page' => $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'listed successfully',
            'success' => true,
            'statusCode' => 200,
            'usersList' =>$dataProvider
        ];
        return $ret;
    }
}