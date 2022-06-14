<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
// use backend\models\Log;
use api\modules\v1\models\Account;
use backend\models\Event;
use backend\models\Music;
use backend\models\Favourite;
use backend\models\EventGiftVoucher;
use backend\models\EventMenuGift;
use backend\models\MenuGift;
use backend\models\MenuGiftItems;
use backend\models\EventParticipant;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;
use common\models\EventFoodVoucher;
use common\models\EventOrder;
use backend\models\BankAccount;
use backend\models\InvoiceNo;
use linslin\yii2\curl;


/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class EventController extends ActiveController
{
    public $modelClass = 'backend\models\Event';
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

                'Access-Control-Allow-Origin' => ["prezenty.in,www.prezenty.in,localhost"], 

                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],

                'Access-Control-Request-Headers' => ['*'],

                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Allow-Methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], 
                'Allow' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],

                'Access-Control-Max-Age' => 86400,

                'Access-Control-Expose-Headers' => []

            ]

        ];

        return $behaviors;

    }
    
    // for web and app
    public function actionCreateEvent(){        
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $statusCheck=false;
        $modelEvent='';
        $ret = [];
        $va_number='';
        $va_upi='';
        $va_ifsc='';
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
 
        $post = Yii::$app->request->post();
        $title = isset($post['title'])?$post['title']:'';
        $date = isset($post['date'])?$post['date']:'';
        $time = isset($post['time'])?$post['time']:'';
        // $gift_voucher_ids = isset($post['gift_voucher_ids'])?$post['gift_voucher_ids']:'';
        // $menu_ids = isset($post['menu_ids'])?$post['menu_ids']:'';
        $music_file_id = isset($post['music_file_id'])?$post['music_file_id']:'';
        $base64 = isset($post['base_64'])?$post['base_64']:'';
        $image = UploadedFile::getInstanceByName('image');
        $music_file = UploadedFile::getInstanceByName('music_file');
        
        if($title && $date && $time){
            $customerId='';
            $bankAcc = BankAccount::find()->where(['user_id'=>$user_details->id,'status'=>1])->one();   
             if(!empty($bankAcc)){
                $customerId = $bankAcc->customer_id;
             } else {
                 $customerId = $user_details->email."-".$user_details->id;
                 $customerId = preg_replace('/(\.|@|#|\$|%|\^|&|\*|!|;|:|\'|"|~|`|\?|=|\+|\(|\))/i', '-', $customerId);
             }
            

            // $buildId=explode("@",$user_details->email);
            $curl = curl_init();
            
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];            
            curl_setopt_array($curl, array(
              CURLOPT_URL => $baseUrlDecentro.'core_banking/account_information/fetch_details?type=virtual&mobile='.$user_details->phone_number, //.'&customer_id='.$customerId.'&qr_requested=1',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                "client_id: $client_id",
                "client_secret: $client_secret",
                "module_secret: $module_secret",
                "provider_secret: $provider_secret"
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $accountRes=json_decode($response);
            
           //  print_r(json_encode($accountRes));exit;
            
            
            $createNewAccount = 1;
            $accountDetAry=[];
            
            // if(empty($accountRes->accounts)){
            //     $createNewAccount = 1;
            // }else{
                foreach ($accountRes->accounts as $key => $value) {
                    if(isset($value->upiOnboardingStatus)){
                        if($value->upiOnboardingStatus == 'SUCCESS'){
                            $accountDetAry[]= $value;
                            $createNewAccount=0;
                            break;
                        }
                    }
                }            
            // }
            
            
            // return $accountDetAry;
            
             if($createNewAccount == 1){
                 
                $curl = curl_init();
                $bank=array();
                $body=array(
                        "bank_codes" => ["YESB"],
                        "name"=> $user_details->name,
                        "email"=> $user_details->email,
                        "mobile"=> $user_details->phone_number,
                        "address"=> $user_details->address,
                        "kyc_verified"=> 1,
                        "kyc_check_decentro"=> 0,
                        "customer_id"=> $customerId, // $buildId[0].'-'.$user_details->id, //'id'.$user_details->id,
                        "master_account_alias" => Yii::$app->params['decentroConfig']['master_account_alias'],
                        "upi_onboarding"=>1,
                        "pan"=>"AAMCP2658N",
                        "state_code"=>12,
                        "city"=>"Bangalore",
                        "pincode"=>560062,
                        
                            // "merchant_category_code"=> "5818",
                            // "merchant_business_type"=> "2",
                            // "transaction_count_limit_per_day"=> 1000,
                            // "transaction_amount_limit_per_day"=> 100000,
                            // "transaction_amount_limit_per_transaction"=> 10000,
                            
                        "virtual_account_balance_settlement"=>"ENABLED",    
                        "upi_onboarding_details" => array(
                            "merchant_category_code"=> "5818",
                            "merchant_business_type"=> "2",
                            "transaction_count_limit_per_day"=> 1000,
                            "transaction_amount_limit_per_day"=> 100000,
                            "transaction_amount_limit_per_transaction"=> 10000
                            )
                        );       
                curl_setopt_array($curl, array(
                          CURLOPT_URL => $baseUrlDecentro.'v2/banking/account/virtual',
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'POST',
                          CURLOPT_POSTFIELDS =>json_encode($body),
                          CURLOPT_HTTPHEADER => array(
                            "Content-Type: application/json",
                            "Accept: */*",
                            "client_id: $client_id",
                            "client_secret: $client_secret",
                            "module_secret: $module_secret"
                        ),
                    ));         
                $response = curl_exec($curl);
                
                // print_r(json_encode(array( "Content-Type: application/json",  "Accept: */*",  "client_id: $client_id",  "client_secret: $client_secret",  "module_secret: $module_secret" )));
                // print_r(json_encode($body));
                // print_r($response); exit;
                $response=json_decode($response);
                curl_close($curl);
    
                if($response->status == 'SUCCESS'){
                    
                    $t= 0;
        
                    foreach ($response->accounts as $key => $value) {
                        if(isset($value->upiOnboardingStatus)){
                            if($value->upiOnboardingStatus == 'SUCCESS'){
                                $t = 1;
                                break;
                            }
                        }
                    }
                    
                    if($t==0){
                        $msg = "Failed to create virtual account with upi";
                        if(isset($response->upiOnboardingStatusDescription)){
                            $msg =$response->upiOnboardingStatusDescription;
                        }
                        return [
                            "baseUrlVideo" => Yii::$app->params['base_path_event_video'],
                            "detail" => null,
                            "message" => $msg,
                            "statusCode" => 200,
                            "success" => false          
                        ];
                    }    
                        
    
                    $statusCheck=true;
                    
                    $va_number=$response->data[0]->accountNumber;
                    $va_upi=$response->data[0]->upiId;
                    $va_ifsc=$response->data[0]->ifsc;
    
                    $bankAcc= new BankAccount;
    
                    $bankAcc->va_bank='YES BANK LTD';
                    $bankAcc->va_number=$response->data[0]->accountNumber;
                    $bankAcc->va_upi=$response->data[0]->upiId;
                    $bankAcc->va_ifsc=$response->data[0]->ifsc;
                    $bankAcc->user_id=$user_details->id;
                    $bankAcc->customer_id=$customerId;
                    $bankAcc->save(false);
    
    
    
                } else {
    
                    $statusCheck=false;
    
                }
    
             } else {
    
                $statusCheck=true;
                //print_r($accountDetAry[0]->accountNumber);exit;
                $bankAcc = BankAccount::find()->where(['va_number'=>$accountDetAry[0]->accountNumber,'va_ifsc' =>$accountDetAry[0]->ifscCode,'status'=>1])->one();
                if(!empty($bankAcc)){
    
                    $va_number=$bankAcc->va_number;
                    $va_upi=$bankAcc->va_upi;
                    $va_ifsc=$bankAcc->va_ifsc;
    
                }else{
    
                    $bankAcc= new BankAccount;
    
                    $bankAcc->va_bank='YES BANK LTD';
                    $bankAcc->va_number=$accountDetAry[0]->accountNumber;
                    $bankAcc->va_upi=$accountDetAry[0]->upiId;
                    $bankAcc->va_ifsc=$accountDetAry[0]->ifscCode;
                    $bankAcc->user_id=$user_details->id; 
                    $bankAcc->customer_id=$customerId;
    
                    $bankAcc->save(false);
    
                    $va_number=$accountDetAry[0]->accountNumber;
                    $va_upi=$accountDetAry[0]->upiId;
                    $va_ifsc=$accountDetAry[0]->ifscCode;
    
                } 
    
             }   
                
            
            if($statusCheck == true){            
                
                $model = new Event;
                // $convertedTime = date('h:i:s',strtotime($time));
                $convertedDate = date('Y-m-d',strtotime($date));
                // $voucherArray = explode(',',$gift_voucher_ids);
                // $menuArray = explode(',',$menu_ids);
                $model->title = $title;
                $model->date = $convertedDate;
                $model->time = $time; // $convertedTime;
                $model->user_id = $user_details->id;
                $model->invite_message = isset($post['invite_message'])?$post['invite_message']:'';
                $model->venue = isset($post['venue'])?$post['venue']:'';
                if($music_file_id){
                    $modelMusic = Music::find()->where(['id'=>$music_file_id])->one();
                    if($modelMusic){
                        $model->music_file_url = $modelMusic->music_file_url;
                    }
                }
                $model->save(false);
                // if($voucherArray){
                //     foreach($voucherArray as $voucherId){
                //         $modelEventGiftVoucher = new EventGiftVoucher;
                //         $modelEventGiftVoucher->event_id = $model->id;
                //         $modelEventGiftVoucher->gift_voucher_id = $voucherId;
                //         $modelEventGiftVoucher->save(false);
                //     }
                // }
                // if($menuArray){
                //     foreach($menuArray as $menuId){
                //         $modelEventMenuGift = new EventMenuGift;
                //         $modelEventMenuGift->event_id = $model->id;
                //         $modelEventMenuGift->menu_gift_id = $menuId;
                //         $modelEventMenuGift->save(false);
                //     }
                // }
                
                $user = new User;
                $video_file = UploadedFile::getInstanceByName('video_file');
                $videoFilesLocation = Yii::$app->params['upload_path_event_video'];            
                if($video_file){
    
                        $saveFile = $user->uploadAndSave($video_file, $videoFilesLocation);
                        if(isset($saveFile)&&$saveFile!=null){
                            
                            $model->video_file = $saveFile;
                        }                
                }
                
                $model->created_by = isset($post['created_by'])?$post['created_by']:'';
                $musicFilesLocation = Yii::$app->params['upload_path_music_files'];
                $imageFilesLocation = Yii::$app->params['upload_path_event_images'];
                $user = new User;
                if($image){
                    $saveFile = $user->uploadAndSave($image, $imageFilesLocation);
                    if(isset($saveFile)&&$saveFile!=null){
                        $model->image_url = $saveFile;
                    }
                }
                if($music_file){
                    $saveFile = $user->uploadAndSave($music_file, $musicFilesLocation);
                    if(isset($saveFile)&&$saveFile!=null){
                        $model->music_file_url = $saveFile;
                    }
                }
                if($base64){
                    $path = $imageFilesLocation;
                    $img = $base64;
                    $img = str_replace('data:image/png;base64,', '', $img);
                    $img = str_replace(' ', '+', $img);
                    $data = base64_decode($img);
                    $imageUrl = 'event-image-'.time() . '.jpeg';
                    $file = '../../common/uploads/event-images/'.$imageUrl;
                    $success = file_put_contents($file, $data);
                    $model->image_url = $imageUrl;
                }
                $model->save(false);
                $model->va_bank='YES BANK LTD';
                $model->va_number=$va_number;
                $model->va_upi=$va_upi;
                $model->va_ifsc=$va_ifsc;
                $model->save(false);
             
    
                // Save event_food_vouchers
                $event_id = $model->id;
                // $foodVouchersPostData = isset($post['food_vouchers_ids'])? $post['food_vouchers_ids'] : [];
                // $foodVouchers = [];
                // foreach($foodVouchersPostData as $fv) {
                //   $foodVouchers[] = [$event_id, $fv];
                // }
                
                // if(count($foodVouchers) > 0) {
                //   Yii::$app->db->createCommand()
                //     ->batchInsert(EventFoodVoucher::tableName(), ['event_id', 'voucher_id'], $foodVouchers)
                //     ->execute();
                // }
    
                $modelEvent = $model;
                $msg = "Event created successfully";
                $success = true;
                
                try{
                    if($user_details->email){
                    $body="";
                    $mailData=array();
                    $appFromEmail = 'support@prezenty.in';
                    $mailData[0]['name']=$user_details->name;
                    $mailData[0]['status']='CREATE';
                    $testArray['mailData']=$mailData;
                       
                    $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/create-update-event' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('Experience the next level of virtual events!')->setTextBody()->send();                       
                        
                    //$mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($user_details->email)->setSubject('Experience the next level of virtual events!')->setTextBody("Dear ".$user_details->name.",Well done. By signing up You've taken your first step towards a happier and smart life.You have successfully created an event via Prezenty. Thanks, Prezenty Team")->send();                
                }
                }catch(\Exception $e){}
            } else {
                $success = false;
                $msg = "Unable to create virtual account. Please contact customer support"; // $response->message;
                $modelEvent=null;
            }
            
        }else{
            $msg = "Title, Date and Time cannot be blank";
        }
        $ret = [
            "baseUrlVideo" => Yii::$app->params['base_path_event_video'],
            "detail" => $modelEvent,
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success          
        ];
        return $ret;
    }
    
    public function actionMyEvents(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
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
        $keyword = isset($get['keyword'])?$get['keyword']:'';
        $is_upcomming = isset($get['is_upcomming'])?$get['is_upcomming']:0;
        $query = Event::find()->where(['status'=>1])->andWhere(['user_id'=>$user_details->id]);
        if($keyword){
            $query->andWhere(['like','title',$keyword]);
        }
        if($is_upcomming){
            $date = date('Y-m-d');
            $query->andWhere(['>','DATE(date)',$date]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ],
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
        ]);
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $events = [];
        foreach($dataProvider->getModels() as $event){
            $modelFavourite = Favourite::find()->where(['status'=>1,'event_id'=>$event->id,'user_id'=>$event->user_id])->one();
            if($modelFavourite){
                $is_favourite = true;
            }else{
                $is_favourite = false;
            }
            $eventList = [
                "id" => $event->id,
                "title" => $event->title,
                "date" => $event->date,
                "time" => $event->time,
                "image_url" => $event->image_url,
                "music_file_url" => $event->music_file_url,
                "user_id" => $event->user_id,
                "status" => $event->status,
                "created_at" => $event->created_at,
                "modified_at" => $event->modified_at,
                "video_file" =>$event->video_file,
                "created_by"=>$event->created_by,
                "invite_message"=>$event->invite_message,
                "venue"=>$event->venue,
                "is_favourite" => $is_favourite
            ];
            $events[] = $eventList;
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $ret = [
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'baseUrlVideo' => Yii::$app->params['base_path_event_video'],
            'statusCode' => 200,
            'list' => $events,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
  
    public function actionEventDetail(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $success = false;
        $message = '';
        $statusCode = 200;
        $isParticipated = false;
        $participantId = null;
        $model = [];
        $modelEventGiftVoucher = [];
        $giftVoucherList = [];
        $menuList = [];
        $modelEventMenuGift = [];
        $eventId = isset($get['event_id'])?$get['event_id']:'';
        $email = isset($get['email'])?$get['email']:'';
        $model = Event::find()->where(['status'=>1,'id'=>$eventId])->one();
        if($model){
            if(!$eventId){
                $message = "Event id cannot be blank";
            }else{
                $model = Event::find()->where(['status'=>1,'id'=>$eventId])->one();
                // $modelEventGiftVoucher = EventGiftVoucher::find()
                // ->leftJoin('gift_voucher','gift_voucher.id=event_gift_voucher.gift_voucher_id')
                // ->where(['event_gift_voucher.status'=>1,'gift_voucher.status'=>1,'event_id'=>$model->id])
                // ->select(['event_gift_voucher.*','gift_voucher.title as title','gift_voucher.image_url as image_url'])
                // ->all();
                // foreach($modelEventGiftVoucher as $giftVocher){
                //     $giftVoucherList[] = [
                //         "id" => $giftVocher->id,
                //         "event_id" => $giftVocher->event_id,
                //         "gift_voucher_id" => $giftVocher->gift_voucher_id,
                //         "gift_voucher_name" => $giftVocher->title,
                //         "gift_voucher_image" => $giftVocher->image_url,
                //         "status" => $giftVocher->status,
                //         "created_at" => $giftVocher->created_at,
                //         "modified_at" => $giftVocher->modified_at
                //     ];
                // }
                // $modelEventMenuGift = EventMenuGift::find()->where(['status'=>1,'event_id'=>$model->id])->all();
                // foreach($modelEventMenuGift as $eventMenuGift){
                //     $menuGift = MenuGift::find()->where(['status'=>1,'id'=>$eventMenuGift->menu_gift_id])->one();
                //     $modelItems = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$menuGift->id])->all();
                //     $menus = [
                //         'id' => $menuGift->id,
                //         'title' => $menuGift->title,
                //         'price' => $menuGift->price,
                //         'rating' => $menuGift->rating,
                //         'is_gift' => $menuGift->is_gift,
                //         'is_veg' => $menuGift->is_veg,
                //         'is_non_veg' => $menuGift->is_non_veg,
                //         'image_url' => $menuGift->image_url,
                //         'event_id' => $eventMenuGift->event_id,
                //         'status' => $eventMenuGift->status,
                //         'created_at' => $eventMenuGift->created_at,
                //         'modified_at' => $eventMenuGift->modified_at,
                //         'items' => $modelItems,
                //     ];
                //     $menuList[] = $menus;
                // }
            }
            $musicFilesLocation = Yii::$app->params['base_path_music_files'];
            $imageFilesLocation = Yii::$app->params['base_path_event_images'];
            // $voucherImageFilesLocation = Yii::$app->params['base_path_voucher_images'];
            // $menuGiftImageFileLocation = Yii::$app->params['base_path_menu_images'];
            if($email){
                $modelEventParticipant = EventParticipant::find()->where(['email'=>$email,'event_id'=>$eventId,'status'=>1])->one();
                if($modelEventParticipant){
                    $isParticipated = true;
                    $participantId = $modelEventParticipant->id;
                }
            }

            // $foodVouchers = $model->getFoodVouchers()->with(['voucher.brand'])->asArray()->all();

            $ret = [
                'success' => true,
                'musicFilesLocation' => $musicFilesLocation,
                'imageFilesLocation' => $imageFilesLocation,
                'baseUrlVideo' => Yii::$app->params['base_path_event_video'],
                // 'brand_image_location' => UPLOADS_PATH . '/food-voucher-brands/',
                // 'voucherImageFilesLocation' => $voucherImageFilesLocation,
                // 'menuGiftImageFileLocation' => $menuGiftImageFileLocation,
                'statusCode' => $statusCode,
                'message' => $message,
                'isParticipated' => $isParticipated,
                'participantId' => $participantId,
                'detail' => $model,
                // 'giftVouchers' => $giftVoucherList,
                // 'menuOrGifts' => $menuList,
                // 'foodVouchers' => $foodVouchers,
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
   
    public function actionUpdateEvent(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
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
        $event_id = isset($post['event_id'])?$post['event_id']:'';
        if($event_id){
            $model = Event::find()->where(['status'=>1,'id'=>$event_id,'user_id'=>$user_details->id])->one();
            if($model){
                $title = isset($post['title'])?$post['title']:'';
                $date = isset($post['date'])?$post['date']:'';
                $time = isset($post['time'])?$post['time']:'';
                $invite_message = isset($post['invite_message'])?$post['invite_message']:'';
                $venue = isset($post['venue'])?$post['venue']:'';
                
                // $gift_voucher_ids = isset($post['gift_voucher_ids'])?$post['gift_voucher_ids']:'';
                // $menu_ids = isset($post['menu_ids'])?$post['menu_ids']:'';
                $music_file_id = isset($post['music_file_id'])?$post['music_file_id']:'';
                $base64 = isset($post['base_64'])?$post['base_64']:'';
                $image = UploadedFile::getInstanceByName('image');
                $music_file = UploadedFile::getInstanceByName('music_file');
                if($time){
                    $convertedTime = date('h:i:s',strtotime($time));
                    $model->time = $convertedTime;
                }
                if($date){
                    $convertedDate = date('Y-m-d',strtotime($date));
                    $model->date = $convertedDate;
                }
                
                if($invite_message){
                    
                    $model->invite_message=$invite_message;
                
                }
                
                if($venue){
                    
                    $model->venue=$venue;
                }
                
                // if($gift_voucher_ids){
                //     $voucherArray = explode(',',$gift_voucher_ids);
                //     if($voucherArray){
                //         $modelEventGiftVoucher = EventGiftVoucher::deleteAll(['event_id'=>$model->id]);
                //         foreach($voucherArray as $voucherId){
                //             $modelEventGiftVoucher = new EventGiftVoucher;
                //             $modelEventGiftVoucher->event_id = $model->id;
                //             $modelEventGiftVoucher->gift_voucher_id = $voucherId;
                //             $modelEventGiftVoucher->save(false);
                //         }
                //     }
                // }
                // if($menu_ids){
                //     $menuArray = explode(',',$menu_ids);
                //     if($menuArray){
                //         $modelEventMenuGift = EventMenuGift::deleteAll(['event_id'=>$model->id]);
                //         foreach($menuArray as $voucherId){
                //             $modelEventMenuGift = new EventMenuGift;
                //             $modelEventMenuGift->event_id = $model->id;
                //             $modelEventMenuGift->menu_gift_id = $voucherId;
                //             $modelEventMenuGift->save(false);
                //         }
                //     }
                // }
                if($title){
                    $model->title = $title;
                }
                if($music_file_id){
                    $modelMusic = Music::find()->where(['id'=>$music_file_id])->one();
                    if($modelMusic){
                        $model->music_file_url = $modelMusic->music_file_url;
                    }
                }
                $model->save(false);

                // Save event_food_vouchers
                $event_id = $model->id;
                // $foodVouchersPostData = isset($post['food_vouchers_ids'])? $post['food_vouchers_ids'] : [];
                // $foodVouchers = [];
                // foreach($foodVouchersPostData as $fv) {
                //   $foodVouchers[] = [$event_id, $fv];
                // }
                
                // if(count($foodVouchers) > 0) {
                //   EventFoodVoucher::deleteAll(['event_id' => $event_id]);
                //   Yii::$app->db->createCommand()
                //     ->batchInsert(EventFoodVoucher::tableName(), ['event_id', 'voucher_id'], $foodVouchers)
                //     ->execute();
                // }

                $musicFilesLocation = Yii::$app->params['upload_path_music_files'];
                $imageFilesLocation = Yii::$app->params['upload_path_event_images'];
                $user = new User;
                if($image){
                    $saveFile = $user->uploadAndSave($image, $imageFilesLocation);
                    if(isset($saveFile)&&$saveFile!=null){
                        $model->image_url = $saveFile;
                    }
                }
                if($music_file){
                    $saveFile = $user->uploadAndSave($music_file, $musicFilesLocation);
                    if(isset($saveFile)&&$saveFile!=null){
                        $model->music_file_url = $saveFile;
                    }
                }
                $user = new User;
                $video_file = UploadedFile::getInstanceByName('video_file');
                $videoFilesLocation = Yii::$app->params['upload_path_event_video'];
                if($video_file){

                    $saveFile = $user->uploadAndSave($video_file, $videoFilesLocation);
                    if(isset($saveFile)&&$saveFile!=null){
                        
                        $model->video_file = $saveFile;
                        if(isset($post['created_by'])){
                            
                            $model->created_by = $post['created_by'];
                        }
                        
                    }                
                }                
                if($base64){
                    $path = $imageFilesLocation;
                    $img = $base64;
                    $img = str_replace('data:image/png;base64,', '', $img);
                    $img = str_replace(' ', '+', $img);
                    $data = base64_decode($img);
                    $imageUrl = 'event-image-'.time() . '.jpeg';
                    $file = '../../common/uploads/event-images/'.$imageUrl;
                    $success = file_put_contents($file, $data);
                    $model->image_url = $imageUrl;
                }
                

                try{
                    if($user_details->email){
    
                    $body="";
                    /*$appFromEmail="support@prezenty.in";
                    $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($user_details->email)->setSubject('Your virtual event has updated well!')->setTextBody("Dear ".$user_details->name.",Well done.You have successfully updated your event via Prezent Prezenty. Thanks, Prezenty Team")->send();   */
                    
                    $mailData=array();
                    $appFromEmail = 'support@prezenty.in';
                    $mailData[0]['name']=$user_details->name;
                    $mailData[0]['status']='UPDATE';
                    $testArray['mailData']=$mailData;
                       
                    $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/create-update-event' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('Experience the next level of virtual events!')->setTextBody()->send();                                           
                    
                } 
                }catch(\Exception $e){}
                
                $model->save(false);
                $msg = "Event updated successfully";
                $success = true;
            }else{
                $msg = "Invalid Event Id";
            }
        }else{
            $msg = "Event Id cannot be blank";
        }
        $ret = [
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success          
        ];
        return $ret;
    }
    public function actionDeleteEvent(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
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
        $id = isset($post['id'])?$post['id']:'';
        if($id){    
            $model = Event::find()->where(['status'=>1,'id'=>$id,'user_id'=>$user_details->id])->one();
            if($model){
                
                if($model->va_number){
                    
                    $modelUser = User::find()->where(['id'=>$model->user_id])->one();
                    $name=str_replace(' ', '', $modelUser->name);
                    $curl = curl_init();
                    
                    $client_id=Yii::$app->params['decentroConfig']['client_id'];
                    $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
                    $module_secret=Yii::$app->params['decentroConfig']['account_module'];
                    $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
                    $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];  
                    
                    $dataArray = array("account_number"=>$model->va_number,"customer_id" =>$name.''.$modelUser->id,"mobile_number" => $modelUser->phone_number);

                    $urlData = http_build_query($dataArray);
            
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
                    
                    //print_r($response);exit;
                    
                    if($response->presentBalance == 0){
                        $model->status = 0;
                        $model->save(false);
                        $success = true;
                        $msg = "Event Deleted successfully";
                        
                    } else {
                        
                        $success = false;
                        $msg = "Event Cannot be deleted!Account Contained amount";
                    }
                        
            } else {
                
                $success = false;
                $msg = "Account number is empty!";
                
            }
            
                
            }else{
                
                $msg = "Invalid Id";
                $success = false;
                
            }
        }else{
            
            $msg = "Id cannot be blank";
            $success = false;
        }
        $ret = [
            'message' => $msg,
            'success' => $success,
            'statusCode' => $statusCode
        ];
        return $ret;
    }
    
    public function actionHome(){
        
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        //print_r($_SERVER);//exit;
        //print_r($headers);exit;
         
         
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
        $keyword = isset($get['keyword'])?$get['keyword']:'';
        $is_upcomming = isset($get['is_upcomming'])?$get['is_upcomming']:0;
        $is_upcomming_invites = isset($get['is_upcomming_invites'])?$get['is_upcomming_invites']:0;
        $query = Event::find()->where(['status'=>1])->andWhere(['user_id'=>$user_details->id]);
        $inviteQuery = Event::find()
        ->leftJoin('event_participant','event_participant.event_id=event.id')
        ->where(['event.status'=>1])
        ->andWhere(['event_participant.email'=>$user_details->email]);
        $query->orderBy(['date' => SORT_DESC]);
        if($keyword){
            $query->andWhere(['like','title',$keyword]);
        }
        if($is_upcomming){
            $date = date('Y-m-d');
            $query->andWhere(['>=','DATE(date)',$date]);
            $query->orderBy(['date' => SORT_DESC]);
        }
        $inviteQuery->orderBy(['event.date' => SORT_DESC]);
        if($keyword){
            $inviteQuery->andWhere(['like','title',$keyword]);
        }
        if($is_upcomming){
            $date = date('Y-m-d');
            $inviteQuery->andWhere(['>=','DATE(event.date)',$date]);
            $inviteQuery->orderBy(['event.date' => SORT_ASC]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $inviteDataProvider = new ActiveDataProvider([
            'query' => $inviteQuery,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $musicFilesLocation = Yii::$app->params['base_path_music_files'];
        $imageFilesLocation = Yii::$app->params['base_path_event_images'];
        $events = [];
        foreach($dataProvider->getModels() as $event){
            $modelFavourite = Favourite::find()->where(['status'=>1,'event_id'=>$event->id,'user_id'=>$event->user_id])->one();
            if($modelFavourite){
                $is_favourite = true;
            }else{
                $is_favourite = false;
            }
            $eventList = [
                "id" => $event->id,
                "title" => $event->title,
                "date" => $event->date,
                "time" => $event->time,
                "image_url" => $event->image_url,
                "music_file_url" => $event->music_file_url,
                "user_id" => $event->user_id,
                "status" => $event->status,
                "created_at" => $event->created_at,
                "modified_at" => $event->modified_at,
                "video_file" =>$event->video_file,
                "created_by"=>$event->created_by,
                "is_favourite" => $is_favourite
            ];
            $events[] = $eventList;
        }
        $invites = [];
        foreach($inviteDataProvider->getModels() as $invite){
            $modelFavourite = Favourite::find()->where(['status'=>1,'event_id'=>$invite->id,'user_id'=>$user_details->id])->one();
            if($modelFavourite){
                $is_favourite = true;
            }else{ 
                $is_favourite = false;
            }
            $inviteList = [
                "id" => $invite->id,
                "title" => $invite->title,
                "date" => $invite->date,
                "time" => $invite->time,
                "image_url" => $invite->image_url,
                "music_file_url" => $invite->music_file_url,
                "user_id" => $invite->user_id,
                "status" => $invite->status,
                "created_at" => $invite->created_at,
                "modified_at" => $invite->modified_at,
                "is_favourite" => $is_favourite
            ];
            $invites[] = $inviteList;
        }
        $ret = [
            'musicFilesLocation' => $musicFilesLocation,
            'imageFilesLocation' => $imageFilesLocation,
            'baseUrlVideo' => Yii::$app->params['base_path_event_video'],
            'statusCode' => 200,
            'eventList' => $events,
            'inviteList' => $invites,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
    public function actionSendSummary(){
/*	    $model=new InvoiceNo();
	    $model->save();
	    //print_r($model->inv_no);exit;
	    return $model->id;*/
                $name='albin';
                $phone='8590102805';
                        
                         $curl = curl_init();
                        
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phone.'&sender=PRZNTY&message=WoW!%20Prezenty%20successfully%20sent%20your%20EGift%20card%20via%20email%20and%20SMS.%20Gift%20card%20No:5%20Activation%20No:5%20Merchant%20Name:4%20Enjoy%20the%20day%20with%20Prezenty.%20To%20explore%20more,%20check%20out%20the%20Prezenty%20web:%20https://prezenty.in/&template_id=1307164760863532508',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            ));
                            $response = curl_exec($curl);
                            curl_close($curl);  
                            
	    /*$appFromEmail="support@prezenty.in";
	    $mailData=array();
        $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/test-mail' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Week Summary')->setTextBody('Summary Details')->send();                */                                                                                                
        
	}    
    public function actionCoinStatement($userid)
    {
        
       /* $query = "SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and user_id = $userid),0)-ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and user_id = $userid),0) amount";*/
       $model = Event::find()->where(['user_id'=>$userid])->all();
      // print_r($model[0]['id']);exit;
        $data=array();
        $sum=0;
        foreach ($model as $key => $value) {
            
            $id=$value['id'];
            $query ="SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and ((user_id = $userid) or (event_id in (select id from event where id = $id))) ),0) amount";

            $command = Yii::$app->db->createCommand($query);
            
            $result = $command->queryAll();
            $data[$key]['id']=$id;
            $data[$key]['Name']=$value['title'];
            $data[$key]['receivedAmt']=$result[0]['amount'];
            $amtGet=$result[0]['amount'];
            
            $query="SELECT ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and  event_id = $id),0) amount";
            $command = Yii::$app->db->createCommand($query);
            $result = $command->queryAll();
            
            $data[$key]['redeemAmt']=$result[0]['amount'];
            
            $data[$key]['balanceEvent']=$amtGet-$result[0]['amount'];
            /*$query ="SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and ((user_id = $userid) or (event_id in (select id from event where id = $id))) ),0)- ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE'  and event_id = $id),0) amount";

            $command = Yii::$app->db->createCommand($query);
            $result = $command->queryAll();
            $data[$key]['totAmt'] = $result[0]['amount'];*/
            
        }
        
        $data[0]['availableCoins'] = $result[0]['amount'];
       
        
        
        $ret = [
            
                'statusCode' => 200,
                'data' => $data,
                'message' => 'Listed Successfully',
                'success' => true
                ];

        return $ret;  
        
        
    } 

    public function actionEventStatement($event_id)
    {
        
            $model = Event::find()->where(['id'=>$event_id])->one();
            
            $query ="SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and ((event_id = $event_id) or (event_id in (select id from event where id = $event_id))) ),0) amount";
            
            $command = Yii::$app->db->createCommand($query);
            
            $result = $command->queryAll();
            
            $data[0]['id']=$model->id;
            $data[0]['Name']=$model->title;
            $data[0]['receivedAmt']=$result[0]['amount'];
            $amtGet=$result[0]['amount'];
            
            $query="SELECT ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and  event_id = $event_id),0) amount";
            $command = Yii::$app->db->createCommand($query);
            $result = $command->queryAll();
            
            $data[0]['redeemAmt']=$result[0]['amount'];
            
            $data[0]['balanceEvent']=$amtGet-$result[0]['amount']; 
            $data[0]['availableCoins'] =  $amtGet-$result[0]['amount'];
            
            $ret = [
                
                    'statusCode' => 200,
                    'data' => $data,
                    'message' => 'Listed Successfully',
                    'success' => true
                    ];
    
            return $ret;              
            
    }    
    
    public function actionEventGiftStatement($event_id)
    {
        
        $result=(new \yii\db\Query())
                        ->select(['tb1.*', 'tb2.name'])
                        ->from('upi_transaction_master as tb1')
                        ->innerJoin('event_participant as tb2', 'tb1.participant_id = tb2.id')
                        ->where(['tb1.event_id'=>$event_id])
                        ->andWhere(['tb1.status'=>'SUCCESS'])
                        ->all();
            
            
            
        $ret = [
            
                'statusCode' => 200,
                'data' => $result,
                'message' => 'Listed Successfully',
                'success' => true
                ];
    
            return $ret;              
            
    }  
    public function actionEventGiftRedeemStatement($event_id)
    {
        
        $result=(new \yii\db\Query())
                        ->select(['tb1.*', 'tb3.name'])
                        ->from('redeem_master as tb1')
                        ->innerJoin('order_detail as tb2', 'tb1.id = tb2.redeem_transaction_id')
                        ->innerJoin('products as tb3', 'tb2.product_id = tb3.id')
                        ->where(['tb1.event_id'=>$event_id])
                        ->andWhere(['tb1.status'=>'COMPLETE'])
                        ->all();
            
            
            
        $ret = [
            
                'statusCode' => 200,
                'data' => $result,
                'message' => 'Listed Successfully',
                'success' => true
                ];
    
            return $ret;              
            
    }    
    	
}
