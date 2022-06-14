<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
// use backend\models\Log;
use api\modules\v1\models\Account;
use backend\models\Event;
use backend\models\BlockedUser;
use backend\models\EventParticipant;
use backend\models\Music;
use backend\models\Notification;
use backend\models\EventGiftVoucher;
use backend\models\GiftVoucher;
use backend\models\EventGiftVoucherReceived;
use backend\models\EventMenuGift;
use backend\models\EventVideoWishesReceived;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;
use common\components\Sms;
use backend\models\UpiTransaction;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class EventParticipantController extends ActiveController
{
    public $modelClass = 'backend\models\EventParticipant';
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


    public function actionCreate(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $detail = [];
        $ret = [];
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $name = isset($post['name'])?$post['name']:null;
        $email = isset($post['email'])?$post['email']:null;
        $phone = isset($post['phone'])?$post['phone']:null;
        $address = isset($post['address'])?$post['address']:null;
        // $members_count = isset($post['members_count'])?$post['members_count']:null;
        // $is_veg = isset($post['is_veg'])?$post['is_veg']:0;
        $need_food = isset($post['need_food'])?$post['need_food']:0;
        $need_gift = isset($post['need_gift'])?$post['need_gift']:0;
        $is_attending = isset($post['is_attending'])?$post['is_attending']:1;
        if($event_id){
            $modelEvent = Event::find()->where(['status'=>1,'id'=>$event_id])->one();
            if($modelEvent){
                if($email){
                    $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'event_id'=>$event_id,'email'=>$email])->one();
                    if($modelEventParticipant){
                        $success = true;
                        $msg = "Participated Successfully";
                        $model = EventParticipant::findOne($modelEventParticipant->id);
                        $detail = $model;
                        $ret = [
                            "message" => $msg,
                            "statusCode" => 200,
                            "success" => $success,
                            "detail" => $detail        
                        ];
                        return $ret;
                    }
                }
                if($phone){
                    $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'event_id'=>$event_id,'phone'=>$phone])->one();
                    if($modelEventParticipant){
                        $success = true;
                        $msg = "Participated Successfully";
                        $model = EventParticipant::findOne($modelEventParticipant->id);
                        $detail = $model;
                        $ret = [
                            "message" => $msg,
                            "statusCode" => 200,
                            "success" => $success,
                            "detail" => $detail        
                        ];
                        return $ret;
                    }
                }
                $modelEventParticipant = new EventParticipant;
                $modelEventParticipant->event_id = $event_id;
                $modelEventParticipant->name = $name;
                $modelEventParticipant->phone = $phone;
                $modelEventParticipant->email = $email;
                $modelEventParticipant->address = $address;
                // $modelEventParticipant->members_count = $members_count;
                // $modelEventParticipant->is_veg = $is_veg;
                $modelEventParticipant->need_food = $need_food;
                $modelEventParticipant->need_gift = $need_gift;
                $modelEventParticipant->is_attending = $is_attending;
                $modelEventParticipant->save(false);

                $title = "New Participant";
                $type = 'message';
                $typeVal = $name.' participated in your event';
                //$dataArr = [];
                $dataArr=$modelEvent;
                
                $modelUser = User::find()->where(['id'=>$modelEvent->user_id])->one();
                $value = $modelUser->email;
                $isGroupMessage = 0;
                
                try{
                    $notification = Yii::$app->notification->sendToOneUser($title,$value,$type,$typeVal,$isGroupMessage,$dataArr);
                    
                } catch (\Exception $e){ 
                    print_r($e); exit;
                }
                $modelNotification = new Notification;
                $modelNotification->event_id = $modelEvent->id;
                $modelNotification->participant_id = $modelEventParticipant->id;
                $modelNotification->type = "participated";
                $modelNotification->type_id = null;
                $modelNotification->message = $name.' participated in your event';
                $modelNotification->save(false);

                $success = true;
                $msg = "Participated Successfully";

                $model = EventParticipant::findOne($modelEventParticipant->id);
                $detail = $model;
            }else{
                $msg = "Invalid event id";
            }
        }else{
            $msg = "Event Id cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
            "detail" => $detail        
        ];
        return $ret;
    }
    public function actionUpdateParticipant(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $detail = [];
        $ret = [];
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        $participant_id = isset($post['participant_id'])?$post['participant_id']:'';
        $name = isset($post['name'])?$post['name']:null;
        $email = isset($post['email'])?$post['email']:null;
        $phone = isset($post['phone'])?$post['phone']:null;
        $address = isset($post['address'])?$post['address']:null;
        if($event_id){
            $modelEvent = Event::find()->where(['status'=>1,'id'=>$event_id])->one();
            if($modelEvent){
                $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'event_id'=>$event_id,'id'=>$participant_id])->one();
                if($modelEventParticipant){
                    $modelEventParticipant->name = $name;
                    $modelEventParticipant->phone = $phone;
                    $modelEventParticipant->email = $email;
                    $modelEventParticipant->address = $address;
                    $modelEventParticipant->save(false);
                    $success = true;
                    $msg = "Participated Successfully";

                    $model = EventParticipant::findOne($modelEventParticipant->id);
                    $detail = $model;
                }else{
                    $msg = "Invalid participant id";
                }
            }else{
                $msg = "Invalid event id";
            }
        }else{
            $msg = "Event Id cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
            "detail" => $detail        
        ];
        return $ret;
    }
    public function actionDetail(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $detail = [];
        $ret = [];
        $id = isset($get['id'])?$get['id']:'';
        if($id){
            $model = EventParticipant::find()->where(['status'=>1,'id'=>$id])->one();
            if($model){
                $success = true;
                $msg = "Listed successfully";
                $detail = $model;
            }else{
                $msg = "Invalid Id";
            }
        }else{
            $msg = "Id cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
            "detail" => $detail        
        ];
        return $ret;
    }
    public function actionGiftList(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $msg = "";
        $statusCode = 200;
        $success = true;
        $detail = [];
        $giftList = [];
        $ret = [];
        $id = isset($get['id'])?$get['id']:'';
        if($id){
            $modelEvent = Event::find()->where(['status'=>1,'id'=>$id])->one();
            if($modelEvent){
                $modelGifts = GiftVoucher::find()
                ->leftJoin('event_gift_voucher','event_gift_voucher.gift_voucher_id=gift_voucher.id')
                ->where(['event_gift_voucher.status'=>1,'gift_voucher.status'=>1])
                ->andWhere(['event_gift_voucher.event_id'=>$modelEvent->id])->all();
                $msg = "Gifts Listed Successfully";
                $detail = $modelEvent;
                $giftList = $modelGifts;
            }else{
                $msg = "Invalid Event Id";
            }
        }else{
            $msg = "Id cannot be blank";
        }
        $baseUrl = Yii::$app->params['base_path_voucher_images'];
        $baseUrlBg = Yii::$app->params['base_path_voucher_images_bg'];
        $ret = [
            "baseUrl" => $baseUrl,
            "baseUrlBg" => $baseUrlBg,
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
            "detail" => $detail,
            "Gifts" => $giftList
        ];
        return $ret;
    }
    public function actionGiftDetail(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $msg = "";
        $statusCode = 200;
        $success = true;
        $detail = [];
        $ret = [];
        $id = isset($get['id'])?$get['id']:'';
        if($id){
            $modelGiftVoucher = GiftVoucher::find()->where(['status'=>1,'id'=>$id])->one();
            if($modelGiftVoucher){
                $msg = "Gifts Listed Successfully";
                $detail = $modelGiftVoucher;
            }else{
                $msg = "Invalid Event Id";
            }
        }else{
            $msg = "Id cannot be blank";
        }
        $baseUrl = Yii::$app->params['base_path_voucher_images'];
        $ret = [
            "baseUrl" => $baseUrl,
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
            "detail" => $detail
        ];
        return $ret;
    }
    public function actionSendGiftVoucher(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $event_gift_id = isset($post['event_gift_id'])?$post['event_gift_id']:'';
        $event_participant_id = isset($post['event_participant_id'])?$post['event_participant_id']:'';
        $amount = isset($post['amount'])?$post['amount']:'';
        if($event_gift_id && $event_participant_id){
            $modelEventGift = GiftVoucher::find()->where(['status'=>1,'id'=>$event_gift_id])->one();
            if($modelEventGift){
                $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$event_participant_id])->one();
                if($modelEventParticipant){
                    $model = new EventGiftVoucherReceived;
                    $model->event_gift_id = $event_gift_id;
                    $model->event_participant_id = $event_participant_id;
                    $model->event_id = $modelEventParticipant->event_id;
                    $model->amount = $amount;
                    $model->save(false);
                    $no = rand(0, 9999999);
                    $rand = str_pad($no, 7, "0", STR_PAD_LEFT);
                    $barcodeType = $rand.time();
                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    file_put_contents('../../common/uploads/barcodes/'.$barcodeType.'.png', $generator->getBarcode($barcodeType, $generator::TYPE_CODE_128));
                    $model->barcode = $barcodeType;
                    $model->save(false);
                    $modelNotification = new Notification;
                    $modelNotification->event_id = $modelEventParticipant->event_id;
                    $modelNotification->participant_id = $event_participant_id;
                    $modelNotification->type = "gift_voucher";
                    $modelNotification->type_id = $modelEventGift->id;
                    $modelNotification->message = $modelEventParticipant->name." sent you a ".$modelEventGift->title." gift voucher";
                    $modelNotification->save(false);

                    $date = date('d-m-Y h:i a');
                    $params['message'] = "Hi, {$modelEventParticipant->name} has send you {$modelEventGift->title} gift voucher worth "
                    . "{$amount} on {$date}. You can redeem the voucher once the amount is active.";
                    Yii::$app->mailer->send($model->event->organizer->email, "Prezenty | Gift voucher for you!", $params);

                    $message = "Hi, {$modelEventParticipant->name} has send you {$modelEventGift->title} gift voucher worth {$amount} on {$date}. "
                      . "You can redeem the voucher once the amount is active. Cocoalabs";
                    Sms::send($model->event->organizer->phone_number, $message, "1307163773511356232");

                    $success = true;
                    $msg = "Gift sent successfully";
                }else{
                    $msg = "Invalid participant Id";
                }
            }else{
                $msg = "Invalid Gift Id";
            }
        }else{
            $msg = "Event gift ID and Event participant ID cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
        ];
        return $ret;
    }
    public function actionSendVideoWishes(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $event_participant_id = isset($post['event_participant_id'])?$post['event_participant_id']:'';
        $caption = isset($post['caption'])?$post['caption']:'';
        $video = UploadedFile::getInstanceByName('video');
        
        if($event_participant_id && $video){
            $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$event_participant_id])->one();
            if($modelEventParticipant){
                $model = new EventVideoWishesReceived;
                $model->event_id = $modelEventParticipant->event_id;
                $model->event_participant_id = $event_participant_id;
                $model->caption = $caption;
                $videoLocation = Yii::$app->params['upload_path_video_wishes'];
                $modelUser = new User;
                $saveVideo = $modelUser->uploadAndSave($video,$videoLocation);
                if($saveVideo){
                    $model->video_url = $saveVideo;
                }
                $model->save(false);
                
                $message = "{$modelEventParticipant->name} sent you a video wish";
                $payload = [
                  'id' => $model->id,
                  'participant_id' => $modelEventParticipant->id,
                  'sender_email' => $modelEventParticipant->email,
                  'date' => $modelEventParticipant->event->date,
                  'time' => $modelEventParticipant->event->time,
                  'event_id' => $modelEventParticipant->event_id,
                  'event_title' => $modelEventParticipant->event->title,
                ];

                Yii::$app->notification->sendToOneUser(
                  $message,
                  $modelEventParticipant->event->organizer->email,
                  'message',
                  $message,
                  false,
                  $payload
                );
                $modelNotification = new Notification;
                $modelNotification->event_id = $modelEventParticipant->event_id;
                $modelNotification->participant_id = $event_participant_id;
                $modelNotification->type = "video_wish";
                $modelNotification->type_id = null;
                $modelNotification->message = $modelEventParticipant->name." sent you a video wish";
                $modelNotification->save(false);
                $success = true;
                $msg = "Video Wish Sent Successfully";
            }else{
                $msg = "Invalid Participant Id";
            }
        }else{
            $msg = "Event participant ID and video cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success,
        ];
        return $ret;
    }
    public function actionVideoWishes(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $get = Yii::$app->request->get();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $dataProvider = [];
        $ret = [];
        $list = [];
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
        $hasNextPage = false;
        $count = 0;
        $id = isset($get['id'])?$get['id']:'';
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        if($id){
            $modelEvent = Event::find()->where(['status'=>1,'id'=>$id])->one();
            if($modelEvent){
                $query = EventVideoWishesReceived::find()->where(['status'=>1,'event_id'=>$id]);
                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => [
                        'pageSizeLimit' => [$page, $per_page]
                    ]
                ]);
                if(($page*$per_page) < ($query->count())){
                    $hasNextPage = true;
                }
                if($dataProvider->getModels()){
                    foreach($dataProvider->getModels() as $model){
                        $modelParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$model->event_participant_id])->one();
                        $list[] = array(
                            'id' => $model->id,
                            'event_name' => $modelEvent->title,
                            'video_url' => $model->video_url,
                            'caption' => $model->caption,
                            'date' => date('d M Y',strtotime($model->created_at)),
                            'name' => ($modelParticipant)?$modelParticipant->name:'',
                            'email' => ($modelParticipant)?$modelParticipant->email:'',
                            'time' => date('h:i A',strtotime($model->created_at))
                        );
                    }
                }
                $count = (int) $query->count();
                $msg = "Listed successfully";
                $success = true;
            }else{
                $msg = "Invalid Event Id";
            }
        }else{
            $modelEvent = Event::find()->where(['status'=>1,'user_id'=>$user_details->id])->one();
            $query = EventVideoWishesReceived::find()
            ->leftjoin('event','event.id=event_video_wishes_received.event_id')
            ->where(['event_video_wishes_received.status'=>1,'event.user_id'=>$user_details->id]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSizeLimit' => [$page, $per_page]
                ]
            ]);
            if(($page*$per_page) < ($query->count())){
                $hasNextPage = true;
            }
            if($dataProvider->getModels()){
                foreach($dataProvider->getModels() as $model){
                    $modelParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$model->event_participant_id])->one();
                    $list[] = array(
                        'id' => $model->id,
                        'event_name' => $modelEvent->title,
                        'video_url' => $model->video_url,
                        'caption' => $model->caption,
                        'date' => date('d M Y',strtotime($model->created_at)),
                        'name' => ($modelParticipant)?$modelParticipant->name:'',
                        'email' => ($modelParticipant)?$modelParticipant->email:'',
                        'time' => date('h:i A',strtotime($model->created_at))
                    );
                }
            }
            $count = (int) $query->count();
            $msg = "Listed successfully";
            $success = true;
        }
        $baseUrl = Yii::$app->params['base_path_video_wishes'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'list' => $list,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => $count,
            'message' => $msg,
            'success' => $success
        ];
        return $ret;
    }
    public function actionParticipants(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $dataProvider = [];
        $list = [];
        $ret = [];
        $hasNextPage = false;
        $count = 0;
        $name = '';
        $email = '';
        $is_blocked = false;
        $id = isset($get['id'])?$get['id']:'';
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $email = isset($get['email'])?$get['email']:'';
        if($id){
            $modelEvent = Event::find()->where(['status'=>1,'id'=>$id])->one();
            $modelUser = User::find()->where(['id'=>$modelEvent->user_id])->one();
            $name = $modelUser->name;
            $useremail = $modelUser->email;
            if($modelEvent){
                $query = EventParticipant::find()->where(['status'=>1,'event_id'=>$id]);
                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => [
                        'pageSizeLimit' => [$page, $per_page]
                    ]
                ]);
                foreach($dataProvider->getModels() as $user){
                    if($email){
                        $modelBlockedUser = BlockedUser::find()->where(['event_id' => $id,'blocked_user_email' => $user['email'], 'blocked_by_user_email' => $email, 'status' => 1])->one();
                        if($modelBlockedUser){
                            $is_blocked = true;
                        }else{
                            $is_blocked = false;
                        }
                    }
                    $list[] = [
                        'id' => $user['id'],
                        'event_id' => $user['event_id'],
                        'status' => $user['status'],
                        'created_at' => $user['created_at'],
                        'modified_at' => $user['modified_at'],
                        'name' => $user['name'],
                        'phone' => $user['phone'],
                        'email' => $user['email'],
                        'address' => $user['address'],
                        // 'members_count' => $user['members_count'],
                        // 'is_veg' => $user['is_veg'],
                        'need_food' => $user['need_food'],
                        'need_gift' => $user['need_gift'],
                        'is_attending' => $user['is_attending'],
                        'is_ordered' => $user['is_ordered'],
                        'is_delivered' => $user['is_delivered'],
                        'is_blocked' => $is_blocked,
                    ];
                }
                if(($page*$per_page) < ($query->count())){
                    $hasNextPage = true;
                }
                $count = (int) $query->count();
                $msg = "Listed successfully";
                $success = true;
            }else{
                $msg = "Invalid Event Id";
            }
        }else{
            $msg = "Id cannot be blank";
        }
        $ret = [
            'statusCode' => 200,
            'name' => $name,
            'email' => $useremail,
            'list' => $list,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => $count,
            'message' => $msg,
            'success' => $success
        ];
        return $ret;
    }
    public function actionParticipantDetail(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $success = false;
        $message = '';
        $statusCode = 200;
        $eventId = isset($get['event_id'])?$get['event_id']:'';
        $participant_id = isset($get['participant_id'])?$get['participant_id']:'';
        if(!$eventId || !$participant_id){
            $msg = 'Event id and participant id cannot be blank';
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => false
            ];
            return $ret;
        }
        $model = Event::find()->where(['status'=>1,'id'=>$eventId])->one();
        if($model){
            $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'event_id'=>$eventId,'id'=>$participant_id])->one();
            if(!$modelEventParticipant){
                $msg = "Invalid Participant Id";
                $ret = [
                    'message' => $msg,
                    'statusCode' => 200,
                    'success' => false
                ];
                return $ret;
            }
            $msg = "Participant detail listed successfully";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => true,
                'detail' => $modelEventParticipant
            ];
            return $ret;
        }else{
            $ret = [
                'success' => false,
                'message' => 'Invalid Event Id',
                'statusCode' => 200
            ];
            return $ret;
        }
    }
    
    public function actionAccountDetOld($event_id)
    {
        $model = Event::find()->where(['id'=>$event_id])->one();

        $modelUser = User::find()->where(['id'=>$model->user_id])->one();
        $dataProvider='';
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $curl = curl_init();
        $name=str_replace(' ', '', $modelUser->name);
        $response=array();
        if($model->va_number){
        $dataArray = array("account_number"=>$model->va_number,"customer_id" =>$name.''.$modelUser->id,"mobile_number" => $modelUser->phone_number);

                    $urlData = http_build_query($dataArray);
                    $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_balance?'.$urlData,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',

                      CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "Accept: */*",
                        "client_id: $client_id",
                        "client_secret: $client_secret",
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));
                    $response = curl_exec($curl);
                    $response=json_decode($response);

                    $result=(new \yii\db\Query())
                                    ->select(['tb1.*', 'tb2.name','tb2.phone'])
                                    ->from('upi_transaction_master as tb1')
                                    ->innerJoin('event_participant as tb2', 'tb1.participant_id = tb2.id')
                                    ->where(['tb1.event_id'=>$event_id])
                                    ->andWhere(['tb1.status'=>'SUCCESS'])
                                    ->andWhere(['not', ['tb1.participant_id' => null]])->all();
                    
                    
                    $ret = [
        
                        'statusCode' => 200,
                        'account' => $response,
                        'data' =>$result,
                        'message' => 'Listed Successfully',
                        'success' => true
                    ];


        
            } else {


                $ret = [
        
                        'statusCode' => 200,
                        'data' => $dataProvider,
                        'message' => 'Event Id cannot Be Null',
                        'success' => false
                    ];
            }

                                                   
             return $ret;

    }
    
    
    public function actionAccountDet($event_id)
    {
        
        $query = "SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and event_id = $event_id),0)-ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and event_id = $event_id),0) amount";

        $command = Yii::$app->db->createCommand($query);
        $result = $command->queryAll();
        $data = $result[0]['amount'];
        
    
        // $model = UpiTransaction::find()->where(['event_id'=>$event_id,'voucher_type' => 'GIFT','status' => 'SUCCESS'])->sum('amount');
        
        $result=(new \yii\db\Query())
                                    ->select(['tb1.*', 'tb2.name','tb2.phone', 'tb2.email'])
                                    ->from('upi_transaction_master as tb1')
                                    ->innerJoin('event_participant as tb2', 'tb1.participant_id = tb2.id')
                                    ->where(['tb1.event_id'=>$event_id])
                                    ->andWhere(['tb1.status'=>'SUCCESS'])
                                    ->andWhere(['not', ['tb1.participant_id' => null]])->all();
        $ret = [
            
                'statusCode' => 200,
                'data' =>$result,
                'amount' => $data,
                'message' => 'Listed Successfully',
                'success' => true
                ];

        return $ret;        

    }   

    public function actionAccountDetCoin($userid)
    {
        
       /* $query = "SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and user_id = $userid),0)-ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and user_id = $userid),0) amount";*/
        $query ="SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and ((user_id = $userid) or (event_id in (select id from event where user_id = $userid))) ),0)- ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and user_id = $userid),0) amount";

        $command = Yii::$app->db->createCommand($query);
        $result = $command->queryAll();
        $data = $result[0]['amount'];
        
        $ret = [
            
                'statusCode' => 200,
                'amount' => $data,
                'message' => 'Listed Successfully',
                'success' => true
                ];

        return $ret;  
        
        
    }    
    
 
 public function actionGetRedeemReport($event_id)
    {
        try{
$query="
select json_arrayagg(json_object(
'image',image,
'payment', payment , 'product', product , 'orders', orders)) result
from (
    select od.id, od.recipient_name
        ,od.recipient_mobile
        ,od.recipient_email
        ,count(*) voucher_count
        ,json_object('id', p.id, 'product_name', p.name, 'image', p.image_mobile) product
        ,(select image from products where id = p.id limit 1) image
        ,json_object('id', rd.id, 'amount', rd.amount, 'status', rd.status, 'created_at', rd.created_at
        ,'inv_no_id', (SELECT distinct inv_no_id FROM order_detail where redeem_transaction_id = rd.id limit 1)) payment
        ,json_arrayagg(json_object( 'id', od.id, 'status', od.status, 'card_no', od.card_number, 'card_pin', od.card_pin,
        'activation_url', od.activation_url, 'activation_code', od.activation_code, 'amount', od.amount,
        'issue_date', od.issuance_date, 'validity', od.validity, 'recipient_name', od.recipient_name, 'recipient_email', od.recipient_email,
        'recipient_mobile', od.recipient_mobile, 'created_at', od.created_at,'request_body', cast(od.request_body as json))) orders
    from order_detail od
        ,products p
        ,redeem_master rd
	where p.id = od.product_id
		and od.redeem_transaction_id = rd.id
		and od.redeem_transaction_id in (
			select id from redeem_master where event_id = $event_id 
            )
    group by payment order by id desc
    ) T";
    
    $command = Yii::$app->db->createCommand($query);
    $result = $command->queryAll();   
    
    $data = $result[0]['result'];
    
    if($data == null){
        $ret = [
            'statusCode' => 200,
            'data' =>[],
            'message' => 'No report',
            'success' => true
            ];
    
        return $ret; 
    }else{
        $ret = [
            'statusCode' => 200,
            'base_path_woohoo_images' => Yii::$app->params['base_path_woohoo_images'],
            'data' =>json_decode($data),
            'message' => 'Listed Successfully',
            'success' => true
            ];
    
        return $ret; 
    }     
    } catch (ErrorException $e) {
        return $e;
    }
}

 public function actionGetRedeemVoucherList($user_id)
    {
try{
$query="
select json_arrayagg(json_object(
'image',image,'jsonData',jsonData,
'payment', payment , 'product', product , 'orders', orders)) result
from (
    select od.id, od.recipient_name
        ,od.recipient_mobile
        ,od.recipient_email
        ,count(*) voucher_count
        ,json_object('id', p.id, 'product_name', p.name, 'image', p.image_mobile) product
        ,(select image from products where id = p.id limit 1) image,
        (select json_data from product_details where product_id = p.id limit 1) jsonData
        ,json_object('id', rd.id, 'amount', rd.amount, 'status', rd.status, 'created_at', rd.created_at
        ,'inv_no_id', (SELECT distinct inv_no_id FROM order_detail where redeem_transaction_id = rd.id limit 1)) payment
        ,json_arrayagg(json_object( 'id', od.id, 'status', od.status, 'card_no', od.card_number, 'card_pin', od.card_pin,
        'activation_url', od.activation_url, 'activation_code', od.activation_code, 'amount', od.amount,
        'issue_date', od.issuance_date, 'validity', od.validity, 'recipient_name', od.recipient_name, 'recipient_email', od.recipient_email,
        'recipient_mobile', od.recipient_mobile, 'created_at', od.created_at,'request_body', cast(od.request_body as json))) orders
    from order_detail od
        ,products p
        ,redeem_master rd
	where p.id = od.product_id
		and od.redeem_transaction_id = rd.id
		and od.redeem_transaction_id in (
			select id from redeem_master where user_id = $user_id 
            )
    group by payment order by id desc
    ) T";
    
    $command = Yii::$app->db->createCommand($query);
    $result = $command->queryAll();   
    
    $data = $result[0]['result'];
    
    if($data == null){
        $ret = [
            'statusCode' => 200,
            'data' =>[],
            'message' => 'No report',
            'success' => true
            ];
    
        return $ret; 
    }else{
        $ret = [
            'statusCode' => 200,
            'base_path_woohoo_images' => Yii::$app->params['base_path_woohoo_images'],
            'data' =>json_decode($data),
            'message' => 'Listed Successfully',
            'success' => true
            ];
    
        return $ret; 
    }     
    } catch (ErrorException $e) {
        return $e;
    }
}      
 public function actionGetSendFoodReport($event_id)
    {
        try{
$query="select json_arrayagg(json_object(
'image',image,
'payment', payment , 'product', product , 'orders', orders)) result
from (
    select od.id, od.recipient_name
        ,od.recipient_mobile
        ,od.recipient_email
        ,count(*) voucher_count
        ,json_object('id', p.id, 'product_name', p.name, 'image', p.image_mobile) product
        ,(select image from products where id = p.id limit 1) image
        ,json_object('id', upi.id, 'amount', upi.amount, 'status', upi.status, 'created_at', upi.created_at
        ,'inv_no_id', (SELECT distinct inv_no_id FROM order_detail where upi_transaction_id = upi.id limit 1)) payment
        ,json_arrayagg(json_object( 'id', od.id, 'status', od.status, 'card_no', od.card_number, 'card_pin', od.card_pin,
        'activation_url', od.activation_url, 'activation_code', od.activation_code, 'amount', od.amount,
        'issue_date', od.issuance_date, 'validity', od.validity, 'recipient_name', od.recipient_name, 'recipient_email', od.recipient_email,
        'recipient_mobile', od.recipient_mobile, 'created_at', od.created_at,'request_body', cast(od.request_body as json) )) orders
    from order_detail od
        ,products p
        ,upi_transaction_master upi
    where p.id = od.product_id
        and od.upi_transaction_id = upi.id
        and od.upi_transaction_id in (
            select id
            from upi_transaction_master
            where event_id = $event_id 
            ) and od.status <> 'ERROR'
    group by payment order by id desc
    ) T";
    
    $command = Yii::$app->db->createCommand($query);
    $result = $command->queryAll();   
    
    $data = $result[0]['result'];
    
    if($data == null){
        $ret = [
            'statusCode' => 200,
            'data' =>[],
            'message' => 'No report',
            'success' => true
            ];
    
        return $ret; 
    }else{
        $ret = [
            'statusCode' => 200,
            'base_path_woohoo_images' => Yii::$app->params['base_path_woohoo_images'],
            'data' =>json_decode($data),
            'message' => 'Listed Successfully',
            'success' => true
            ];
    
        return $ret; 
    }     
    } catch (ErrorException $e) {
        return $e;
    }
}
  
  
 public function actionGetUserBoughtVouchers($user_id)
    {
        try{
$query = "
select json_arrayagg(json_object(
'image', image,
'is_gifted', is_gifted, 
				'inv_no_id', inv_no_id, 'id', id, 'status', status, 'card_no', card_number, 'card_pin', card_pin, 'activation_url', activation_url,
				'activation_code', activation_code, 'amount', amount, 'issue_date', issuance_date, 'validity', validity, 
				'recipient_name', recipient_name, 'recipient_email', recipient_email, 'recipient_mobile', recipient_mobile,
				'created_at', created_at,'request_body', cast(request_body as json),
				'product', cast(json_data as json))) result
from (select (case when (select email from user where id = $user_id limit 1) <> recipient_email then 1 else 0 end) is_gifted, 
				 od.inv_no_id, od.id, od.status,  od.card_number,  od.card_pin,  od.activation_url,
				 od.activation_code,  od. amount,  od.issuance_date, od.validity, 
				  od.recipient_name,  od. recipient_email,  od.recipient_mobile,
				  od.created_at, cast(od.request_body as json) request_body,
				  cast(pd.json_data as json) json_data,
				  (select image from products where id = pd.product_id limit 1) image
				  
from order_detail od, product_details pd
where od.user_id = $user_id
	and od.order_type = 'BUY' and pd.product_id = od.product_id and od.status <> 'ERROR' 
order by od.id desc)T;	
";
    
    $command = Yii::$app->db->createCommand($query);
    $result = $command->queryAll();   
    
    $data = $result[0]['result'];
    if($data == ''){
        $data = '[]';
    }
    
    $ret = [
        'statusCode' => 200,
        'data' => json_decode($data),
        'base_path_woohoo_images' => Yii::$app->params['base_path_woohoo_images'],
        'message' => 'Listed Successfully',
        'success' => true
        ];

    return $ret; 
    } catch (ErrorException $e) {
        return $e;
    }

}
  
      
 public function actionGetUserReceivedVouchers($user_id)
    {
        try{
$query="
select json_arrayagg(json_object(
'image', image,
'gifted_by', gifted_by, 
				'inv_no_id', inv_no_id,'id', id, 'status', status, 'card_no', card_number, 'card_pin', card_pin, 'activation_url', activation_url,
				'activation_code', activation_code, 'amount', amount, 'issue_date', issuance_date, 'validity', validity, 
				'recipient_name', recipient_name, 'recipient_email', recipient_email, 'recipient_mobile', recipient_mobile,
				'created_at', created_at,'request_body', cast(request_body as json),
				'product', cast(json_data as json))) result
from (
select (select json_object('name', name, 'email', email, 'phone',concat( country_code,phone_number)) from user where id = od. user_id limit 1) gifted_by, 
				 od.inv_no_id, od.id, od.status,  od.card_number,  od.card_pin,  od.activation_url,
				 od.activation_code,  od. amount,  od.issuance_date, od.validity, 
				  od.recipient_name,  od. recipient_email,  od.recipient_mobile,
				  od.created_at, cast(od.request_body as json) request_body,
				  cast(pd.json_data as json) json_data,
				  (select image from products where id = pd.product_id limit 1) image
				  
from order_detail od, product_details pd
where od.user_id <> $user_id
	and od.recipient_email = (select email from user where id = $user_id limit 1)
	-- and od.order_type = 'BUY' 
	and pd.product_id = od.product_id and od.status <> 'ERROR'
order by od.id desc)T;	";
    
    $command = Yii::$app->db->createCommand($query);
    $result = $command->queryAll();   
    
    $data = $result[0]['result'];
    if($data == ''){
        $data = '[]';
    }
    
    $ret = [
        'statusCode' => 200,
        'data' => json_decode($data),
         'base_path_woohoo_images' => Yii::$app->params['base_path_woohoo_images'],
        'message' => 'Listed Successfully',
        'success' => true
        ];

    return $ret; 
        } catch (ErrorException $e) {
            return $e;
        }
    
}
  

}
