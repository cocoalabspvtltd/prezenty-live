<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;
use common\components\Sms;
use backend\models\UpiTransaction;
use common\models\EventOrderInvoice;
use backend\models\Event;
use backend\models\User;
use backend\models\OrderDetail;
use backend\models\Products;
use linslin\yii2\curl;
use backend\models\EventParticipant;

use backend\models\TransferMaster;
use backend\models\Notification;


class DecentroApiController extends ActiveController
{
    public $modelClass = 'backend\models\UpiTransaction';
    
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

    public function actionCreateUpi(){

        header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: *');
        
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $detail = [];
        $ret = [];

        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['module_secret'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];

            $curl = curl_init();
            $bank=array();
            $body=array(
                    "reference_id" => $post['reference_id'],
                    "payee_account"=> $post['payee_account'],
                    "amount"=> round(floatval($post['amount']),2),
                    "purpose_message"=> $post['purpose_message'],
                    "generate_qr"=> 1,
                    "expiry_time"=> 10,
                    "customized_qr_with_logo"=> 0,
                    "generate_uri"=> 1,
                    );
            curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'v2/payments/upi/link',
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
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));         
                
            $response = curl_exec($curl);
            
                // print_r($response);exit;
            $response=json_decode($response); 
            curl_close($curl);
            
            if($response->status == 'SUCCESS'){
            $msg = "Created Successfully";
            $model=new UpiTransaction;
            $model->reference_id=isset($post['reference_id'])?$post['reference_id']:'';
            $model->decentroTxnId=$response->decentroTxnId;
            $model->participant_id=isset($post['participant_id'])?$post['participant_id']:'';;
            $model->user_id=isset($post['user_id'])?$post['user_id']:'';
            $model->voucher_type=isset($post['voucher_type'])?$post['voucher_type']:'';
            $model->amount=isset($post['amount'])?$post['amount']:'';
            $model->event_id=isset($post['event_id'])?$post['event_id']:'';
            $model->status=$response->data->transactionStatus;
            $model->created_at=date('Y-m-d H:i:s');
            $model->woohoo_id=isset($post['woohoo_id'])?$post['woohoo_id']:'';
            $model->state=isset($post['state'])?$post['state']:'';
            $model->save(false);

            $return=array();
            $return['encodedDynamicQrCode']=$response->data->encodedDynamicQrCode;
            $return['upiUri']=$response->data->upiUri;
            $return['id']=$model->id;
            
            // $amount=isset($post['amount'])?$post['amount']:'.';
            
            // $UserD=User::find()->where(['id'=> $post['user_id']])->one();
            // $phoneSmsSend=$UserD->phone_number;
            // $curl = curl_init();
            // curl_setopt_array($curl, array(
            // CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Thank%20you!%20Your%20payment%20of%20'.$amount.'%20from%20Prezenty%20is%20confirmed.%20Find%20more%20gifts%20to%20shop%20with%20the%20Prezenty%20web:%20https://prezenty.in&template_id=1307164785056981250',
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => '',
            // CURLOPT_MAXREDIRS => 10,
            // CURLOPT_TIMEOUT => 0,
            // CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => 'GET',
            // ));
            
            // $response = curl_exec($curl);
            // curl_close($curl);             

            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => true,
                'detail' => $return,
                'response' => $response
            ];
        } else {

            $ret = [
                'message' => 'Failed',
                'statusCode' => 200,
                'success' => false,
                'detail' => $response
            ];

        }
            
            return $ret;

    }

    public function actionTransactionStatus($id = null,$order_id= null)
    {
    	$model = UpiTransaction::find()->where(['id'=>$id])->one();

    	if($model){

		$client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['module_secret'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];

        $curl = curl_init();  
        curl_setopt_array($curl, array(
          CURLOPT_URL => $baseUrlDecentro."v2/payments/transaction/".$model->decentroTxnId."/status",
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
            
            // print_r($response);exit;
            
            curl_close($curl);
            $date = date('Y-m-d H:i:s');
			$dateTimeObject1 = date_create($model->created_at); 
			$dateTimeObject2 = date_create($date); 
			
			// Calculating the difference between DateTime objects
			$interval = date_diff($dateTimeObject1, $dateTimeObject2); 
			  
			$minutes = $interval->days * 24 * 60;
			$minutes += $interval->h * 60;
			$minutes += $interval->i;

             $curl = curl_init();
            
            $model = UpiTransaction::find()->where(['id'=>$id])->one();
            $eventId=$model->event_id;
            $participant_id=$model->participant_id;
            $amountR = $model->amount;      
            $modelEvent = Event::find()->where(['id'=>$eventId])->one();;
            $user_details=User::find()->where(['id' => $modelEvent->user_id])->one();  
            
            $phoneSmsSend=$user_details->phone_number;
            
            if($minutes > 10){

            	$model = UpiTransaction::find()->where(['id'=>$id])->one();
                $amount = $model->amount;
                $eventId=$model->event_id;
                
            	if($model->status != 'SUCCESS'){
					
					$model->status=$response->data->transactionStatus;
					$model->transaction_status_description=$response->data->transactionStatusDescription;
					$model->transaction_status_description=$response->data->bankReferenceNumber;
					$model->npci_txnId=$response->data->npciTxnId;
            		$model->save(false);

                    // $invoice = new EventOrderInvoice();

                    // 	if($model->voucher_type == "FOOD"){

                            //   $model = EventOrder::findOne($order_id);

                            //       $invoice->order_id = $model->id;
                            //       $invoice->amount = $model->amount;
                            //       $invoice->service = $model->service;
                            //       $invoice->gst = $model->gst;
                            //       $invoice->cess = $model->cess;
                            //       $invoice->total_amount = $model->total_amount;
                            //       $invoice->save();
                            //   try{    
                                    
                            //         $modelEvent = Event::find()->where(['id'=>$eventId])->one();;
                            //         $user_details=User::find()->where(['id' => $modelEvent->user_id])->one();
                                        
                            //             $appFromEmail="support@prezenty.in";
                                       
                            //             $mailData=[];
                            //             $mailData['phone']=$user_details->phone_number;
                            //             $mailData['date']=date('d-m-Y h:i a');
                            //             $mailData['amount']=$model->amount;
                            //             $mailData['id']=$id;
                            //             $modelOrderDet = OrderDetail::find()->where(['upi_transaction_id' => $id])->one();
                            //             $productDet=Products::find()->where(['id' => $modelOrderDet->product_id])->one();
                            //             $mailData['voucher']=$productDet->name;
                            //             $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($user_details->email)->setSubject('Prezenty Order Details')->setTextBody('Invoice Details')->send();                                                                        
                                        
                            //             $phoneSmsSend=$user_details->phone_number;
                            //             $amountR=$model->amount;
                                          
                                        
                            //   } catch (\Exception $e){ }
                                   
                                


                        // }elseif($model->voucher_type == "GIFT"){
                            
                                // $modelEvent = Event::find()->where(['id'=>$eventId])->one();
                                // $modelEvent->e_amount=$modelEvent->e_amount+$amount;
                                // $amountR=$modelEvent->e_amount+$amount;
                                // $modelEvent->save(false);
                                // try{    
                                //   $user_details=User::find()->where(['id' => $modelEvent->user_id])->one();
                                //     if($user_details->email){
                                //         $body="";
                                //     $participant_id_name = EventParticipant::find()->where(['id'=>$participant_id])->one();
                                //     $message ="You Received Rs.".$amountR." from ".$participant_id_name->name;
                                //     $payload=[];
                                //         Yii::$app->notification->sendToOneUser(
                                //             $message,
                                //             $user_details->email,
                                //             null,
                                //             $message,
                                //             false,
                                //             $payload
                                //           );
                                //     }   
                            //   } catch (\Exception $e){ }   
                                    
                             //$amount=isset($post['amount'])?$post['amount']:'.';

                           /*  $phoneSmsSend=$user_details->phone_number;
                                    $curl = curl_init();
                                    curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Thank%20you!%20Your%20payment%20of%20'.$amountR.'%20from%20Prezenty%20is%20confirmed.%20Find%20more%20gifts%20to%20shop%20with%20the%20Prezenty%20web:%20https://prezenty.in&template_id=1307164785056981250',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'GET',
                                    ));
                            
                             $response = curl_exec($curl);
                             curl_close($curl);             */                             
                            
                        // }
            	$ret = [
                	'message' => 'Success',
                	'statusCode' => 200,
                	'success' => true,
                	'detail' => $model
            		]; 
            	} else {

                    $model->status=$response->data->transactionStatus;
                    $model->transaction_status_description=$response->data->transactionStatusDescription;
                    $model->transaction_status_description=$response->data->bankReferenceNumber;
                    $model->npci_txnId=$response->data->npciTxnId;
                    $model->save(false);
                    
                    $ret = [
                        'message' => 'Success',
                        'statusCode' => 200,
                        'success' => true,
                        'detail' => $model
                        ];                    

                } 

            } else {

                if($response->data->transactionStatus == "SUCCESS"){

                    $model = UpiTransaction::find()->where(['id'=>$id])->one();
                    $model->status=$response->data->transactionStatus;
                    $model->transaction_status_description=$response->data->transactionStatusDescription;
                    $model->transaction_status_description=$response->data->bankReferenceNumber;
                    $model->npci_txnId=$response->data->npciTxnId;
                    $model->save(false);
                    
                    if($model->voucher_type == "GIFT"){
                            
                                $modelEvent = Event::find()->where(['id'=>$eventId])->one();
                                $modelEvent->e_amount = $modelEvent->e_amount+($model->amount);
                                $modelEvent->save(false);
                                
                                try{    
                                   $user_details=User::find()->where(['id' => $modelEvent->user_id])->one();
                                    if($user_details->email){
                                        $body="";
                                    $participant_id_name = EventParticipant::find()->where(['id'=>$participant_id])->one();
                                    
                                    $message ="You Received Rs.".$amountR." from ".$participant_id_name->name;
                                    
                                    $payload=[
                                        'event_id' => $modelEvent->id,
                                        'type' => "receive_event_gift"
                                    ];
            
                                      $notification = Yii::$app->notification->sendToOneUser(
                                            $message,
                                            $user_details->email,
                                            null,
                                            $message,
                                            false,
                                            $payload
                                          );
                                    }
                               } catch (\Exception $e){ 
                                    // print_r($e); exit;
                               } 
                               
                                try{
                                    $modelNotification = new Notification;
                                    $modelNotification->event_id = $modelEvent->id;
                                    $modelNotification->participant_id = $participant_id;
                                    $modelNotification->type = "receive_event_gift";
                                    $modelNotification->type_id = null;
                                    $modelNotification->message = $message;
                                    $modelNotification->save(false);
                                }catch(\Exception $e){
                                    // return $e;
                                }
                                  
                               
                            try{
                                $phoneSmsSend = $participant_id_name->phone;
                            curl_setopt_array($curl, array(
                            CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Thank%20you!%20Your%20payment%20of%20'.$amountR.'%20from%20Prezenty%20is%20confirmed.%20Find%20more%20gifts%20to%20shop%20with%20the%20Prezenty%20web:%20https://prezenty.in&template_id=1307164785056981250',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            ));            
                            $response1 = curl_exec($curl);
                            curl_close($curl); 
                        } Catch(\Exception $e){}
                               
                               
                    }else{
                        try{
                            curl_setopt_array($curl, array(
                            CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Thank%20you!%20Your%20payment%20of%20'.$amountR.'%20from%20Prezenty%20is%20confirmed.%20Find%20more%20gifts%20to%20shop%20with%20the%20Prezenty%20web:%20https://prezenty.in&template_id=1307164785056981250',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            ));            
                            $response1 = curl_exec($curl);
                            curl_close($curl); 
                        } Catch(\Exception $e){}
                    }
                    
                    $ret = [
                        'message' => 'Success',
                        'statusCode' => 200,
                        'success' => true,
                        'detail' => $model
                    ];
                }else{
                    $ret = [
                        'message' => 'Status not success',
                        'statusCode' => 200,
                        'success' => false,
                    ];
                }
            }
    	} else {

    		$ret = [
                'message' => 'No data Found',
                'statusCode' => 200,
                'success' => false,
                'detail' => ''
            ];

    	}

    	return $ret;
    }

    public function actionMoneyTransfer(){

            header('Access-Control-Allow-Origin: *');
            $ret=array();
            $post = Yii::$app->request->post();

            $modelEvent = Event::find()->where(['status'=>1 ,'id'=>$post['id']])->one();
            $modelUser = User::find()->where(['id' => $modelEvent->user_id])->one();
            
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];            
            $curl = curl_init();
            $bank=array();
            $datetime=date("Y-m-d H-i-s");
            $reference_id=$modelEvent->id.'-Redeem-'.date("His");
            $body=array(
                    "reference_id" => $reference_id,
                    "purpose_message"=> $post['message'],
                    "from_customer_id"=> $modelUser->name.''.$modelUser->id,
                    "from_account"=> $modelEvent->va_number,
                    "to_customer_id"=> 'veena255',
                    "to_account"=>'462515801244338015' ,
                    "mobile_number"=> $modelUser->phone_number,
                    "email_address"=> $modelUser->email,
                    "name"=> $modelUser->name,
                    "transfer_type"=> 'NEFT',
                    "transfer_amount"=> $post['amount'],
                    "transfer_datetime"=> $datetime,
                    "frequency"=> "weekly",
                    "end_datetime"=> date("Y-m-d H-i-s", strtotime('+1 hours')),
                    "currency_code" => "INR",                     
                    );

            $body['beneficiary_details']=array(
                    
                    "email_address" => 'prezentyapp@gmail.com',
                    "mobile_number"=> '9544855856',
                    "address"=> 'kkk',
                    "ifsc_code"=> 'YESB0CMSNOC',
                    "country_code"=> "IN",
            );
//print_r(json_encode($body));exit;
            curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/initiate',
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
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));                        
            $response = curl_exec($curl);
            $response=json_decode($response); 
            curl_close($curl);
            
        if(isset($response->error)){

            $model=new TransferMaster;
            $model->decentroTxnId=$response->decentroTxnId;
            $model->status=$response->error->status;
            $model->reference_id=$reference_id;
            $model->purpose_message=$post['message'];
            $model->from_customer_id=$modelUser->name.''.$modelUser->id;
            $model->to_account='462515056346649420';
            $model->mobile_number=$modelUser->phone_number;
            $model->email_address=$modelUser->email;
            $model->name=$modelUser->name;
            $model->transfer_type='NEFT';
            $model->transfer_amount=$post['amount'];
            $model->transfer_datetime=$datetime;
            $model->save(false);

            $ret = [
                        'message' => $response->error->message,
                        'statusCode' => 200,
                        'status' => $response->error->status,
                        'success' => false,
                        'id'      => $model->id,                  
                    ]; 

        } else {

            $model=new TransferMaster;
            $model->decentroTxnId=$response->decentroTxnId;
            $model->status=$response->status;
            $model->reference_id=$reference_id;
            $model->purpose_message=$post['message'];
            $model->from_customer_id=$modelUser->name.''.$modelUser->id;
            $model->to_account='462515056346649420';
            $model->mobile_number=$modelUser->phone_number;
            $model->email_address=$modelUser->email;
            $model->name=$modelUser->name;
            $model->transfer_type='NEFT';
            $model->transfer_amount=$post['amount'];
            $model->transfer_datetime=$datetime;
            $model->save(false);

            $ret = [
                        'message' => $response->message,
                        'statusCode' => 200,
                        'success' => true,
                        'status' =>  $response->status,
                        'id' => $model->id,                     
                    ];

        }

        return $ret;       

    }


    public function actionCheckMoneyTransferStatus($id = null){

        $modelTransfer = TransferMaster::find()->where(['id'=>$id])->one(); 

            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $curl = curl_init();  
            $dataArray = array("reference_id"=>$modelTransfer->reference_id,"decentro_txn_id" =>$modelTransfer->decentroTxnId);
            $urlData = http_build_query($dataArray);

            curl_setopt_array($curl, array(
              CURLOPT_URL =>$baseUrlDecentro.'core_banking/money_transfer/?'.$urlData,
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
                curl_close($curl); 
                //print_r($response);exit; 
                
                $ret = [
                            'message' => 'Listed Succesfully',
                            'statusCode' => 200,
                            'success' => true,
                            'data' =>  $response,
                ];      
                
                return $ret;

    }
    /*
        @atk
        15/02/2022
    */

    public function actionReFundRequest($id = null)
    {
        $model = UpiTransaction::find()->where(['id'=>$id])->one();
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['module_secret'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret']; 
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
        
        $baseUrl =$baseUrlDecentro.'v2/payments/upi/refund';

        $body=array(

            "reference_id" =>$model->reference_id,
            "transaction_id" =>$model->decentroTxnId,
            "purpose_message" =>'Refund Request'

        );
            $curl = curl_init();   
            curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrl,
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
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret",
                    ),
                ));                        
            $response = curl_exec($curl);
            $response=json_decode($response);
            return $response;
            curl_close($curl);
            $ret = [

                    'message' => $response->status,
                    'statusCode' => 200,
                    'success' => true,
                    'data' =>  $response,
                    'id' => $model->id,                     
            ]; 
            
            return $ret;

    }    
    public function actionSendMail()
    {   
        $model = UpiTransaction::find()->where(['id'=>17])->one();
        $amount = $model->amount;
        $eventId=$model->event_id;
        $modelEvent = Event::find()->where(['id'=>$eventId])->one();

        $modelEvent->e_amount=$modelEvent->e_amount+$amount;
        $modelEvent->save(false);
        
        $user_details=User::find()->where(['id' => $modelEvent->user_id])->one();
        $appFromEmail="support@prezenty.in";
                                       
                                        $mailData=[];
                                        $mailData['phone']=$user_details->phone_number;
                                        $mailData['date']=date('d-m-Y h:i a');
                                        $mailData['amount']=$modelEvent->e_amount+$amount;
                                        $mailData['id']=5;
                                       /// $modelOrderDet = OrderDetail::find()->where(['upi_transaction_id' => $id])->one();
                                        $productDet=Products::find()->where(['id' => '1'])->one();
                                        $mailData['voucher']=$productDet->name;
                                        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Order Details')->setTextBody('test')->send();                                    
        
    }
}