<?php

namespace api\modules\v1\controllers;



use Yii;

use yii\web\Controller;

use yii\rest\ActiveController;

use linslin\yii2\curl;

use yii\base\ErrorException;

use common\components\CustomHelper;

use yii\helpers\Url;

use backend\models\OrderDetail;

use backend\models\Redeem;

use backend\models\RzpPayments;

use backend\models\User;

use backend\models\Products;

use backend\models\UpiTransaction;

use Mpdf\Mpdf;

use backend\models\TaxSettings;

use backend\models\OrderMaster;

use backend\models\InvoiceNo;

/**

 * Woohoo Order Create Controller

 */



class OrderCreateController extends ActiveController

{
    public $modelClass = 'backend\models\OrderDetail';
	public function actions()

    {
        
        $actions = parent::actions();

        unset($actions['create']);
        
        unset($actions['redeem']);
        
        unset($actions['balance']);

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



//     public function actionCreateBak()
//     {
        
//     	try {

//             header('Access-Control-Allow-Origin: *');
//             header('Access-Control-Allow-Headers: *');

//     		$baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders';

//             //$requestHeader = Yii::$app->request->headers;
            
//             $verify = CustomHelper::getToken();

//             $postData = Yii::$app->request->post();
            
// /*            if(!isset($requestHeader['authorization']))

//             {

//                 $code = 401;

//                 Yii::$app->response->statusCode = 401;

//                 $message = "Authorization token is not valid.";

//                 $result = [

//                     'result'     => [

//                         'authorization' => 'Invalid'

//                     ],

//                     'statusCode' => $code,

//                     'message'    => $message

//                 ];

  

//                 return $result;

//             }*/


//             $filterParamData = array();
//             $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'POST',$postData); 
//             $dateAtClient = CustomHelper::getDateAtClient();


//             if($signature && $signature != null)
//             {
//                 $curl = new curl\Curl();
//                 //'authorization'=> $requestHeader['authorization'];
                
//                 $response = $curl->setRawPostData(json_encode(

//                 $postData))->setHeaders([

//                             'Content-Type' => 'application/json',
                            
//                             'Accept' => '*/*',

//                             'signature' => $signature,

//                             'dateAtClient' => $dateAtClient,

//                             'authorization'=> 'Bearer '.$verify->token

//                         ])->post($baseUrl,true);

//             }

//             $data = (array) json_decode($response,true);
            
            
//             if($data){
//                 if(isset($data['code'])){

//                   $ret = [                    
//                             'statusCode' => 200,
//                             'data' => json_decode($response),
//                             'message' => 'Invalid',
//                             'success' => false
//                         ];

//                     return $ret;                       
//                 }
                
//                 /*              
//                 commented by albin for muliple mail
                
//                 $mailData = [];
//                 $mailData['logo'] = Url::base(true).'/ic_logo.png'; ;
//                 $mailData['card_number'] = $data['cards'][0]['cardNumber'];
//                 $mailData['card_pin'] = $data['cards'][0]['cardPin'];
//                 $validity = '';
//                 if($data['cards'][0]['validity'] != '' || $data['cards'][0]['validity'] != null){
//                      $d = new \DateTime($data['cards'][0]['validity']);
//                      $d->format('u');
//                      $validity = $d->format('d-F-Y');
//                 }
               

//                 $mailData['card_validity'] = $validity;
//                 $mailData['name'] = $data['cards'][0]['recipientDetails']['name'];
//                 $mailData['gift_card'] = $data['cards'][0]['productName'];
//                 $mailData['currency_symbol'] = $data['currency']['symbol'];
//                 $mailData['price'] = $data['cards'][0]['amount'];

//                 $sku = $data['cards'][0]['sku'];
//                 $mailData['image'] = $data['products'][$sku]['images']['base'];
//                 $mailData['content'] = '';
//                 */
                
                
//                  $ret = [                    
//                     'statusCode' => 200,
//                     'data' => json_decode($response),
//                     'message' => 'Success',
//                     'success' => true
//                 ];
//                 return $ret;
                
//                 $modelRedeem = Redeem::find()->where(['id'=>$post['redeem_transaction_id']])->one();
//                 $modelRedeem->status=$data['status'];
//                 $modelRedeem->save(false);
                
//                 if($data['status'] == 'COMPLETE') {
//                 $mailData[0]['logo'] = Url::base(true).'/ic_logo.png';
                
//                 foreach ($data['cards'] as $key => $value) {

//                     $mailData[$key]['card_number'] = $value['cardNumber'];
//                     $mailData[$key]['card_pin'] = $value['cardPin'];
//                     $mailData[$key]['activationCode'] = $value['activationCode'];
//                     $mailData[$key]['activationUrl'] = $value['activationUrl'];
//                     $validity = '';
//                     if($value['validity'] != '' || $value['validity'] != null){
//                          $d = new \DateTime($value['validity']);
//                          $d->format('u');
//                          $validity = $d->format('d-F-Y');
//                     }
                   

//                     $mailData[$key]['card_validity'] = $validity;
//                     $mailData[$key]['name'] = $value['recipientDetails']['name'];
//                     $mailData[$key]['gift_card'] = $value['productName'];
//                     $mailData[$key]['currency_symbol'] = $data['currency']['symbol'];
//                     $mailData[$key]['price'] = $value['amount'];

//                     $sku = $value['sku'];                    
                                
//                 $mailData[$key]['image'] = $data['products'][$sku]['images']['base'];
//                 $mailData[$key]['content'] = '';
//                 }                

//                 //Get Product details by sku
//                  $sbaseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/products/'.$sku;
//                  $skusignature = CustomHelper::getSignature($sbaseUrl,[],'GET');
//                  if($skusignature && $skusignature != null)
//                     {
//                         $curl = new curl\Curl();
//                         $sresponse = $curl->setHeaders([
//                                 'Content-Type' => 'application/json',
//                                 'signature' => $skusignature,
//                                 'dateAtClient' => $dateAtClient,
//                                 'authorization'=> $requestHeader['authorization']
//                             ])->get($sbaseUrl,true);
//                         $sdata = (array) json_decode($sresponse,true);
//                         if($sdata){
//                             $mailData[0]['content'] = $sdata['tnc']['content'];
//                         }
//                     }
//                 //End

//                 // Save order details into db
//                 /*
//                 commented by albin on 1/02/2022
                
//                 $order = new OrderDetail();
//                 $order->sku = $sku;
//                 $order->order_id = $data['orderId'];
//                 $order->reference_number = $data['refno'];
//                 $order->status = $data['status'];
//                 $order->card_number = $data['cards'][0]['cardNumber'];
//                 $order->card_pin = $data['cards'][0]['cardPin'];
//                 $order->activation_code = $data['cards'][0]['activationCode'];
//                 $order->barcode = $data['cards'][0]['barcode'];
//                 $order->activation_url =  $data['cards'][0]['activationUrl'];
//                 $order->amount = $data['cards'][0]['amount'];
//                 $order->validity = $data['cards'][0]['validity'];
//                 $order->issuance_date = $data['cards'][0]['issuanceDate'];
//                 $order->card_id = $data['cards'][0]['cardId'];
//                 $order->recipient_name = $data['cards'][0]['recipientDetails']['name'];
//                 $order->recipient_email = $data['cards'][0]['recipientDetails']['email'];
//                 $order->recipient_mobile = $data['cards'][0]['recipientDetails']['mobileNumber'];
//                 $order->recipient_country = $postData['address']['country'];
//                 $order->save();
//                 */
//                 //end

//                 foreach ($data['cards'] as $key => $value) {

//                     $order = new OrderDetail();
                    
//                         $order->sku = $sku;
//                         $order->order_id = $data['orderId'];
//                         $order->reference_number = $data['refno'];
//                         $order->status = $data['status'];
//                         $order->card_number = $value['cardNumber'];
//                         $order->card_pin = $value['cardPin'];
//                         $order->activation_code = $value['activationCode'];
//                         $order->barcode = $value['barcode'];
//                         $order->activation_url =  $value['activationUrl'];
//                         $order->amount = $value['amount'];
//                         $order->validity = $value['validity'];
//                         $order->issuance_date = $value['issuanceDate'];
//                         $order->card_id = $value['cardId'];
//                         $order->recipient_name = $value['recipientDetails']['name'];
//                         $order->recipient_email = $value['recipientDetails']['email'];
//                         $order->recipient_mobile = $value['recipientDetails']['mobileNumber'];
//                         $order->recipient_country = $postData['address']['country'];
//                         $order->request_body=$post['body'];
//                         $order->user_id=$post['user_id'];
//                         $order->product_id=$post['product_id'];
//                         $order->order_type=$post['order_type'];
//                         $order->upi_transaction_id=$post['upi_transaction_id'];
//                         $order->redeem_transaction_id=$post['redeem_transaction_id'];
//                         $order->rzp_payment_id=$post['rzp_payment_id'];                        
//                         $order->save();                    


//                 } 
                
//                 $testArray['mailData']=$mailData;
                
//                 $appFromEmail = 'prezentyapp@gmail.com';
//                 $toEmail = $data['cards'][0]['recipientDetails']['email'];
//                 //   // $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-complete' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo(['albinworkz@gmail.com',$toEmail])->setCc('support@prezentystore.com')->setSubject('Prezenty Order Details')->setTextBody('test')->send();
               
//                 $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-complete' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo(['orders@prezentystore.com',$toEmail])->setCc('support@prezentystore.com')->setSubject('Prezenty Order Details')->setTextBody('Order Details')->send();
             
            
//                 } else {
                    
//                     if(isset($data['orderId'])){
                        
//                         $order = new OrderDetail();
//                         $order->sku = $postData['products'][0]['sku'];
//                         $order->order_id = $data['orderId'];
//                         $order->reference_number = $data['refno'];
//                         $order->status = $data['status'];
//                         $order->amount = $postData['payments']['amount'];
//                         $order->recipient_name = $postData['address']['firstname'];
//                         $order->recipient_email = $postData['address']['email'];
//                         $order->recipient_mobile = $postData['address']['telephone'];
//                         $order->recipient_country = $postData['address']['country'];
//                         $order->request_body=$post['body'];
//                         $order->user_id=$post['user_id'];
//                         $order->product_id=$post['product_id']; 
//                         $order->order_type=$post['order_type'];
//                         $order->upi_transaction_id=$post['upi_transaction_id'];
//                         $order->redeem_transaction_id=$post['redeem_transaction_id'];
//                         $order->rzp_payment_id=$post['rzp_payment_id'];                        
//                         $order->save();
                        
//                     }
                
//                 } 
                
                
//             //  $ret = [                    
//             //         'statusCode' => 200,
//             //         'data' => json_decode($response),
//             //         'message' => 'Success',
//             //         'success' => true
//             //     ];
//                 // return $ret;
//             }else{
//                  $ret = [                    
//                         'statusCode' => 200,
//                         'data' => json_decode($response),
//                         'message' => 'no data',
//                         'success' => false
//                     ];
//             return $ret; 
//             }
        
//         //   $ret = [                    
//         //             'statusCode' => 200,
//         //             'data' => json_decode($response),
//         //             'message' => 'Success',
//         //             'success' => true
//         //         ];

//             return $ret; 
//         } catch (ErrorException $e) {

//             return $e;

//         }

//     }


    public function actionCreate()
    {
           header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: *');
    	try {

          

            $postData = Yii::$app->request->post();
            
            $orderBody = $postData['orderBody'];
            $OrderMasterCheckInv='';
            
            if($postData['upi_transaction_id']!=null){
                
                $OrderMasterCheckInv=OrderMaster::find()->where(['upi_transaction_id'=>$postData['upi_transaction_id']])->one();
                /*echo '<pre>';
                print_r($postData['upi_transaction_id']);
                print_r($OrderMasterCheckInv);exit;*/
                
            } else if($postData['rzp_payment_id']!=null){
                
               
                $OrderMasterCheckInv=OrderMaster::find()->Where(['rzp_payment_id'=>$postData['rzp_payment_id']])->one();                
            }
            
            
            $InvMasterId='';
            
            if(!empty($OrderMasterCheckInv))  {
                
                if(isset($OrderMasterCheckInv->upi_transaction_id)){
                    
                    $InvMasterId=$OrderMasterCheckInv->inv_no_id;
                }
                  
                
            } else {
                
                $model=new InvoiceNo();
	            $model->save();
                $InvMasterId='prznty_'.$model->id;                
            }
            
            
            $OrderMaster = new OrderMaster(); 
            $OrderMaster->user_id=$postData['user_id'];
            $OrderMaster->rzp_payment_id=$postData['rzp_payment_id'];
            $OrderMaster->product_id=$postData['product_id'];
            $OrderMaster->upi_transaction_id=$postData['upi_transaction_id'];
            $OrderMaster->redeem_transaction_id=$postData['redeem_transaction_id'];
            $OrderMaster->order_type=$postData['order_type'];
            $OrderMaster->inv_no_id=$InvMasterId;
            $OrderMaster->request_body=json_encode($orderBody);
            $OrderMaster->amount = $orderBody['payments'][0]['amount'];
            $OrderMaster->save();
            $OrderMasterId=$OrderMaster->id;
            
    		$baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders';
            $verify = CustomHelper::getToken();
			//$orderBody = $postData['orderBody'];
            $filterParamData = array();
            $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'POST',$orderBody);
            
            $dateAtClient = CustomHelper::getDateAtClient();

            
            if($signature && $signature != null)
            {
                $curl = new curl\Curl();
                $response = $curl
                -> setRawPostData(json_encode($orderBody))
                -> setHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*',
                    'signature' => $signature,
                    'dateAtClient' => $dateAtClient,
                    'authorization'=> 'Bearer '.$verify->token ]) 
                -> post($baseUrl,true);
            }
        
            $data = (array) json_decode($response,true);
            
            
            // print_r($baseUrl);
            // print_r($orderBody);
            // print_r($response); exit;
            
            
            if($data){
                if(isset($data['code'])){
                   $ret = [                    
                            'statusCode' => 200,
                            'data' => json_decode($response),
                            'message' => 'Invalid',
                            'success' => false
                        ];
                    return $ret;                       
                }
                
                $ret = [                    
                    'statusCode' => 200,
                    'data' => json_decode($response),
                    'message' => 'Success',
                    'success' => true
                ];
                
                 if($postData['order_type'] == 'REDEEM'){ 
                     
                    $modelRedeem = Redeem::find()->where(['id'=>$postData['redeem_transaction_id']])->one();
                    $modelRedeem-> status=$data['status'];
                    $modelRedeem-> state=$postData['state'];
                    $modelRedeem-> save(false); 
                        
                 }
                
                if(isset($data['code'])){
                    
                        /*$order = new OrderDetail();
                        $order->sku = $orderBody ['products'][0]['sku'];
                        $order->status = 'Failure';
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$postData['user_id'];
                        $order->product_id=$postData['product_id'];
                        $order->order_type=$postData['order_type'];
                        $order->upi_transaction_id=$postData['upi_transaction_id'];
                        $order->redeem_transaction_id=$postData['redeem_transaction_id'];
                        $order->rzp_payment_id=$postData['rzp_payment_id']; 
                        $order->status_response=json_encode($data);
                        $order->save();*/
                        
                        $OrderMaster=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                        if(isset($data['message'])){
                            
                            $OrderMaster->message=$data['message'];
                            
                        } else {
                            
                            $OrderMaster->message='FAILED';
                        }
                        $OrderMaster->status='FAILED';
                        $OrderMaster->save(false);
                    
                } else {
                    
                if($data['status'] == 'COMPLETE') {
                    $nameSmsRec='';
                    $phoneSmsRec='';
                    $phoneSmsSend='';
                    $nameSmsSend='';
                    $emailSmsRec='';
                    $OrderMaster=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                    $OrderMaster->status='COMPLETE';
                    $OrderMaster->order_id = $data['orderId'];
                    $OrderMaster->inv_no_id = $InvMasterId;
                    $OrderMaster->save(false);
                    
                    foreach ($data['cards'] as $key => $value) {
                        $order = new OrderDetail();
                        
                        $order->sku = $orderBody ['products'][0]['sku'];
                        $order->order_id = $data['orderId'];
                        $order->reference_number = $data['refno'];
                        $order->status = $data['status'];
                        $order->card_number = $value['cardNumber'];
                        $order->card_pin = $value['cardPin'];
                        $order->activation_code = $value['activationCode'];
                        $order->barcode = $value['barcode'];
                        $order->activation_url =  $value['activationUrl'];
                        $order->amount = $value['amount'];
                        $order->validity = $value['validity'];
                        $order->issuance_date = $value['issuanceDate'];
                        $order->card_id = $value['cardId'];
                        $order->recipient_name = $value['recipientDetails']['name'];
                        $order->recipient_email = $value['recipientDetails']['email'];
                        $order->recipient_mobile = $value['recipientDetails']['mobileNumber'];
                        $order->recipient_country = $orderBody['address']['country'];
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$postData['user_id'];
                        $order->product_id=$postData['product_id'];
                        $order->order_type=$postData['order_type'];
                        $order->upi_transaction_id=$postData['upi_transaction_id'];
                        $order->redeem_transaction_id=$postData['redeem_transaction_id'];
                        $order->rzp_payment_id=$postData['rzp_payment_id'];
                        
                        $order->inv_no_id=$InvMasterId;
                        
                        $order->status_response=json_encode($data); 
                        
                        $order->save(false);
                        
                        $nameSmsRec=$value['recipientDetails']['name'];
                        $phoneSmsRec=$value['recipientDetails']['mobileNumber'];
                        $emailSmsRec=$value['recipientDetails']['email'];
                       
                       try{
                            $country_code=91;
                            $phoneSmsRec = preg_replace("/^\+?{$country_code}/", '',$phoneSmsRec);
                            $curl = curl_init();
                           
                             
                            $v1=(isset($value['cardNumber'])) ? $value['cardNumber'] : "empty";
    
                            $v2=(isset($value['activationCode'])) ? $value['activationCode'] : "empty";
                            
                            $v3=(isset($value['productName'])) ? $value['productName'] : "empty";
    
                            $v3 = str_replace(' ', '%20', $v3);
    
                            $sndSms = CustomHelper::sendSmsCard($phoneSmsRec,$v1,$v2,$v3);
                            } catch (ErrorException $e) {
                        }
                       
                        }
            
                    
                    // send mail
                    try{
                       
                        $query = "select json_data from product_details where product_id = ".$postData['product_id']." limit 1";
                        $command = Yii::$app->db->createCommand($query);
                        $result = $command->queryAll();
                        
                        $UserD=User::find()->where(['id'=> $postData['user_id']])->one();
                        $phoneSmsSend=$UserD->phone_number;
                        $nameSmsSend=$UserD->name;
                        $mailData[0]['content'] = json_decode($result[0]['json_data']) -> tnc -> content;
                        $mailData[0]['logo'] = Url::base(true).'/ic_logo.png';
                        $mailData[0]['giftedBy']=$UserD->name;
                        $mailData[0]['invNo']=$InvMasterId;
                        $mailData[0]['id']=$InvMasterId;
                        $mailData[0]['mob']=$UserD->phone_number;
                        $mailData[0]['email']=$UserD->email;
                        
                        $mailData[0]['voucherImg'] = CustomHelper::getWoohooVoucherImageUrl($postData['product_id']);
                        
                        if($postData['order_type']=='FOOD'){
                            $mailData[0]['id']='FOOD_'.$postData['upi_transaction_id'];
                        } else if($postData['order_type'] == 'BUY'){
                            $mailData[0]['id']='BUY_'.$postData['rzp_payment_id'];
                        } else {
                            $mailData[0]['id']='REDEEM_'.$postData['redeem_transaction_id'];   
                        }
                        
                        foreach ($data['cards'] as $key => $value) {
        
                            $mailData[$key]['card_number'] = $value['cardNumber'];
                            $mailData[$key]['card_pin'] = $value['cardPin'];
                            $mailData[$key]['activationCode'] = $value['activationCode'];
                            $mailData[$key]['activationUrl'] = $value['activationUrl'];
                            $validity = '';
                            if($value['validity'] != '' || $value['validity'] != null){
                                 $d = new \DateTime($value['validity']);
                                 $d->format('u');
                                 $validity = $d->format('d-F-Y');
                            }
                           
                            $mailData[$key]['card_validity'] = $validity;
                            $mailData[$key]['name'] = $value['recipientDetails']['name'];
                            $mailData[$key]['gift_card'] = $value['productName'];
                            $mailData[$key]['amount'] = $value['amount'];
                            $mailData[$key]['currency_symbol'] = $data['currency']['symbol'];
                            $mailData[$key]['price'] = $value['amount'];
        
                            $sku = $value['sku'];                    
                                            
                            $mailData[$key]['image'] = $data['products'][$sku]['images']['mobile'];
                            // $mailData[$key]['voucherImg'] = $data['products'][$sku]['images']['mobile'];
                            
                            // $mailData[$key]['content'] = '';
                        }                
            
                        $testArray['mailData']=$mailData;
                        $appFromEmail = 'support@prezenty.in';
                        $toEmail = $data['cards'][0]['recipientDetails']['email'];
                       // print_r($toEmail);exit;
                       /* $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-complete' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('You got a gift from '.$mailData[0]['giftedBy'])->setTextBody('Order Details')->send();*/
                       
                        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/card-details' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('You got a gift from '.$mailData[0]['giftedBy'])->setTextBody('Order Details')->send();                       
                         
                    } catch (ErrorException $e) {
                    }
                    
                    try{
               /*
                SMS -Sender 3
               */
               
                $sndSms = CustomHelper::sendSmsRecipient($phoneSmsSend,$nameSmsRec);
                /*
                    SMS -Sender 2
                */                                    
                    
                    $refSmsNo='';
                    if(isset($postData['upi_transaction_id'])){
                        
                        $refSmsNo=$postData['order_type'].'_'.$postData['upi_transaction_id'];
                        
                    } else if(isset($postData['redeem_transaction_id'])) {
                        
                        $refSmsNo=$postData['order_type'].'_'.$postData['redeem_transaction_id'];
                        
                    } else {
                        
                        $refSmsNo=$postData['order_type'].'_'.$postData['rzp_payment_id'];
                    }
                    
                    
                    $dateSms=date("d-m-Y");
                    
                $sndSms = CustomHelper::sendSmsSender($phoneSmsSend,$nameSmsSend,$InvMasterId,$refSmsNo,$dateSms);
                    } catch (ErrorException $e) {
                    }
                } else {
                    if(isset($data['orderId'])){
                        
                        /*$order = new OrderDetail();
                        $order->sku = $orderBody ['products'][0]['sku'];
                        $order->order_id = $data['orderId'];
                        $order->reference_number = $data['refno'];
                        $order->status = $data['status'];
                        $order->amount = $orderBody['payments'][0]['amount'];
                        $order->recipient_name = $orderBody['address']['firstname'];
                        $order->recipient_email = $orderBody['address']['email'];
                        $order->recipient_mobile = $orderBody['address']['telephone'];
                        $order->recipient_country = $orderBody['address']['country'];
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$orderBody['user_id'];
                        $order->product_id=$orderBody['product_id']; 
                        $order->order_type=$orderBody['order_type'];
                        $order->upi_transaction_id=$orderBody['upi_transaction_id'];
                        $order->redeem_transaction_id=$orderBody['redeem_transaction_id'];
                        $order->rzp_payment_id=$orderBody['rzp_payment_id']; 
                        
                        $order->status_response=json_encode($data);
                        $order->save();*/
                        
                        $OrderMaster=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                        $OrderMaster->status=$data['status'];
                        $OrderMaster->order_id = $data['orderId'];
                        $OrderMaster->inv_no_id=$InvMasterId;
                        $OrderMaster->save(false);
                    
                    }else{
                                
                        /*$order = new OrderDetail();
                        $order->sku = $orderBody ['products'][0]['sku'];
                        $order->status = 'ERROR';
                        $order->status_response=json_encode($data);
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$postData['user_id'];
                        $order->product_id=$postData['product_id'];
                        $order->order_type=$postData['order_type'];
                        $order->upi_transaction_id=$postData['upi_transaction_id'];
                        $order->redeem_transaction_id=$postData['redeem_transaction_id'];
                        $order->rzp_payment_id=$postData['rzp_payment_id']; 
                       
                        $order->save();*/
                        
                        $OrderMaster=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                        $OrderMaster->order_id=$data['orderId'];
                        $OrderMaster->status='ERROR';
                        $OrderMaster->inv_no_id=$InvMasterId;
                        $OrderMaster->save(false);                        
                
                        }
                    } 
                }
            }else{
                $ret = [
                        'statusCode' => 200,
                        'data' => json_decode($response),
                        'message' => 'no data',
                        'success' => false
                    ];
                return $ret; 
            }
        
            return $ret; 
        } catch (Exception $e) {
            return $e;
        }

    }


    public function actionBalance()
    {
        header('Access-Control-Allow-Origin: *');
        try {
            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/balance';
            $requestHeader = Yii::$app->request->headers;
            $postData = Yii::$app->request->post();
            $verify = CustomHelper::getToken();

            $filterParamData = array();
            $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'POST',$postData);
            $dateAtClient = CustomHelper::getDateAtClient();

            if($signature && $signature != null)
            {
                $curl = new curl\Curl();
                $response = $curl->setRawPostData(json_encode(
                $postData))->setHeaders([
                            'Content-Type' => 'application/json',
                            'signature' => $signature,
                            'dateAtClient' => $dateAtClient,
                            'authorization'=> 'Bearer '.$verify->token
                        ])->post($baseUrl,true);
            }

            $data = (array)json_decode($response,true);
            
            if(isset($data['code'])){
                   $ret = [                    
                            'statusCode' => 200,
                            'data' => $data,
                            'message' => 'Card balance not found',
                            'success' => false
                    ];                     
            }else{
                $ret = [                    
                        'statusCode' => 200,
                        'data' => $data,
                        'message' => 'Success',
                        'success' => true
                    ];
            }
            return $ret;           
        } catch (ErrorException $e) {
            return $e;
        }
    }
    
    
    // public function actionTransactionHistory()
    // {
    //     header('Access-Control-Allow-Origin: *');
    //     try {
    //         $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/transaction/history';
    //         $requestHeader = Yii::$app->request->headers;
    //         $postData = Yii::$app->request->post();
    //         $verify = CustomHelper::getToken();

    //         $filterParamData = array();
    //         $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'POST',$postData);
    //         $dateAtClient = CustomHelper::getDateAtClient();

    //         if($signature && $signature != null)
    //         {
    //             $curl = new curl\Curl();
    //             $response = $curl->setRawPostData(json_encode(
    //             $postData))->setHeaders([
    //                         'Content-Type' => 'application/json',
    //                         'signature' => $signature,
    //                         'dateAtClient' => $dateAtClient,
    //                         'authorization'=> 'Bearer '.$verify->token
    //                     ])->post($baseUrl,true);
    //         }

    //         $data = (array)json_decode($response,true);
            
    //         if(isset($data['code'])){
    //               $ret = [                    
    //                         'statusCode' => 200,
    //                         'data' => $data,
    //                         'message' => 'Card balance not found',
    //                         'success' => false
    //                 ];                     
    //         }else{
    //             $ret = [                    
    //                     'statusCode' => 200,
    //                     'data' => $data,
    //                     'message' => 'Success',
    //                     'success' => true
    //                 ];
    //         }
    //         return $ret;           
    //     } catch (ErrorException $e) {
    //         return $e;
    //     }
    // }


    public function actionSendMail()
    {
        $toEmail = 'vaibhavsharma3070@gmail.com';
        $appFromEmail = 'orders@prezentystore.com';

        $mailData = [];
        $mailData['logo'] = 'http://prezenty.ae/backend/web/ic_logo.png';//Url::base(true).'/ic_logo.png'; ;
        $mailData['card_number'] = 'test';
        $mailData['card_pin'] = 'test';
        $mailData['card_validity'] = 'test';
        $mailData['name'] = 'test';
        $mailData['gift_card'] = 'test';
        $mailData['currency_symbol'] = 'test';
        $mailData['price'] = 'test';
        $mailData['content'] = 'test';
        $mailData['image'] = 'https://images.pexels.com/photos/1303082/pexels-photo-1303082.jpeg?auto=compress&amp;cs=tinysrgb&amp;dpr=2&amp;h=650&amp;w=940';


        $mail = Yii::$app->mailer->compose( [ 'html' => '@app/views/mail/order-complete' ] ,$mailData)->setFrom([$appFromEmail => 'Vaibhav Sharma'])->setTo($toEmail)->setSubject('Prezenty Order Details')->setTextBody('test body')->send();
        $response = [];
        if( $mail == true){
            $response['status'] = 'success';
            $response['message'] = 'Mail Sent';
        }else{
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong!!!';
        }

       $ret = [                    
                'statusCode' => 200,
                'data' => json_decode($response),
                'message' => 'Success',
                'success' => true
            ];

        return $ret; 
    }
    
    public function actionGetOrderDet()
    {
        
 
    $det = OrderDetail::find()
        ->innerJoin('products','products.id=order_detail.product_id')
         ->select(['products.*', 'order_detail.*'])
        ->all();

    $det=(new \yii\db\Query())
                            ->select(['tb1.*', 'tb2.*'])
                            ->from('order_detail as tb1')
                            ->innerJoin('products as tb2', 'tb1.product_id = tb2.id')
                            ->all();
    $ret = [                    
                'statusCode' => 200,
                'data' => $det,
                'message' => 'Success',
                'success' => true
            ];

        return $ret;  



    }
    
    public function actionRedeem(){
        
       
        //header('Access-Control-Allow-Origin: *');
        
        $postData = Yii::$app->request->post();
        $amount=$postData['amount'];
        $eventId=$postData['event_id'];
        $userID=$postData['user_id'];


        $redeem = new Redeem();

        $redeem->event_id=$eventId;
        $redeem->amount=$amount;
        $redeem->user_id=$userID;
        $redeem->status='PENDING';

        $redeem->save(false);
        
        $ret = [                    
                'statusCode' => 200,
                'redeem_id' => $redeem->id,
                'message' => 'Success',
                'success' => true
            ];

        return $ret;          

    }
    
    public function actionSendInvoice(){
    
         $postData = Yii::$app->request->post();
         $mailData=[];
         $appFromEmail="support@prezenty.in";
         $email='';
         
         $model = OrderDetail::find()->where(['rzp_payment_id'=>$postData['id'],'order_type'=>'BUY'])->all();
         if($model){

            $modelRazor=RzpPayments::find()->where(['payment_id' => $model[0]->rzp_payment_id])->all();
            
            $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
            $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'--';
                $temp[0]['date']=$modelRazor[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRazor[0]['amount'];
                $temp[0]['id']='BUY_'.$modelRazor[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'--';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['email']=$modelUser[0]['email'];
                $temp[0]['count']=count($model);
              
                $amount= count($model)*$model[0]['amount'];
                $gst = $modelTax->option_value;
                
                $rzpCharge = $amount * .02;
                $rzpChargeTax = $rzpCharge * ($gst/100);
        
                $temp[0]['serviceCharge'] = $rzpCharge + $rzpChargeTax;
                //$temp[0]['gst']=$modelTax->option_value;
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                //print_r($mailData);exit;

         } else {
            
            $model = OrderDetail::find()->where(['upi_transaction_id'=>$postData['id'],'order_type'=>'FOOD'])->all();    
            if($model){
                
               $temp=array();
                
                foreach ($model as $key => $value) {
                
                
                    $modelUpi=UpiTransaction::find()->where(['id' => $value->upi_transaction_id])->all();
                    $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                    $modelProduct=Products::find()->where(['id' => $value->product_id])->all();

                    $modelUser=User::find()->where(['id' => $modelUpi[0]['user_id']])->all();
                
                    $appFromEmail="support@prezenty.in";
                                           
                    
                    $temp[$key]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                    $temp[$key]['date']=$model[0]->created_at;
                    $temp[$key]['amountR']=$modelUpi[0]['amount'];
                    $temp[$key]['amount']=$model[0]->amount;
                    $temp[$key]['id']='FOOD_'.$modelUpi[0]['id'];
                    $temp[$key]['status']=$value->status;
                    $temp[$key]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                    $temp[$key]['name']=$modelUser[0]['name'];
                    $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                    $temp[$key]['gst']=$modelTax->option_value;
                    $temp[$key]['email']=$modelUser[0]['email'];
                    $temp[$key]['count']=count($model);
                    $temp[$key]['serviceCharge']=0;
                    //$temp[$key]['gst']=0;
                    
                    $temp[$key]['voucher']=$modelProduct[0]['name'];
            }
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                
                
            } else {
                
                $model = OrderDetail::find()->where(['redeem_transaction_id'=>$postData['id'],'order_type'=>'REDEEM'])->all(); 
                
                $modelRedeem=Redeem::find()->where(['id' => $model[0]['redeem_transaction_id']])->all();
                $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                $temp[0]['date']=$model[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRedeem[0]['amount'];
                $temp[0]['id']='REDEEM_'.$modelRedeem[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                $temp[0]['name']=$modelUser[0]['name'];
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                $temp[0]['email']=$modelUser[0]['email'];
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                $temp[0]['serviceCharge']=0;
                //$temp[0]['gst']=0;
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $email=$modelUser[0]['email'];
                
                $mailData['mailData']=$temp;
                
            } 
         }
        //$email='albinsunny1996@gmail.com';
        //$email='maneshvmohanan@gmail.com';
        
            //$mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Order Summary')->setTextBody('Invoice Details')->send();            
        
            //$mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/tax-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();                            
        
        $this->renderPartial('@common/mail/tax-invoice',$mailData);
        $mpdf=new mPDF();
        $mpdf->WriteHTML($this->renderPartial('@common/mail/tax-invoice',$mailData));
        
        $location= Yii::$app->basePath.'/web/pdf/';
        $path = $mpdf->Output($location.'TaxInvoice.pdf', \Mpdf\Output\Destination::FILE);
        $mail = Yii::$app->mailer->compose()->attach($location.'TaxInvoice.pdf')->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();
        
            $ret = [                    
                'statusCode' => 200,
                'message' => 'Success',
                'success' => true
            ];

        return $ret;         
         
         

    }

    public function actionSendInvoiceByType(){
    
         $postData = Yii::$app->request->post();
         $mailData=[];
         $status='';
         $appFromEmail="support@prezenty.in";
         $email='';
         if($postData['order_type'] == 'BUY'){
            
            $model = OrderMaster::find()->where(['rzp_payment_id'=>$postData['id'],'order_type'=>'BUY'])->all();
            
            
            $modelRazor=RzpPayments::find()->where(['payment_id' => $model[0]->rzp_payment_id])->all();

            $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
            $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'--';
                $temp[0]['date']=$modelRazor[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['state']=$modelRazor[0]->state;
                $temp[0]['amountR']=$modelRazor[0]['amount'];
                $temp[0]['type']='BUY';
                $temp[0]['id']='BUY_'.$modelRazor[0]['payment_id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'--';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                $temp[0]['invNo']=$model[0]->inv_no_id;
                $temp[0]['order_id']=$model[0]->order_id;
                // $temp[0]['image']=$modelProduct[0]['image_small'];
                $temp[0]['image'] = CustomHelper::getWoohooVoucherImageUrl($model[0]['product_id']);
            if($model[0]->status == 'COMPLETE'){
                
                $modelOrderDetail = OrderDetail::find()->where(['rzp_payment_id'=>$postData['id'],'order_type'=>'BUY'])->all();
                $temp[0]['rece_name']=$modelOrderDetail[0]->recipient_name;
                $temp[0]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                $temp[0]['rece_email']=$modelOrderDetail[0]->recipient_email;
                
                 $status='COMPLETE';
                 $temp[0]['status']='COMPLETE'; 
            
            } else {
                
                $temp[0]['status']='FAILED';  
                $status='FAILED';
            }
                
                
            
                $amount= count($model)*$model[0]['amount'];
                $gst = $modelTax->option_value;
                
                $rzpCharge = $amount * .02;
                $rzpChargeTax = $rzpCharge * ($gst/100);
                     
                $temp[0]['serviceCharge']=round($rzpCharge,2);
                $temp[0]['taxAmount']=round($rzpChargeTax,2);
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                //print_r($mailData);exit;

         } else if($postData['order_type'] == 'FOOD') {
            
            $model = OrderMaster::find()->where(['upi_transaction_id'=>$postData['id'],'order_type'=>'FOOD'])->all();
            

               $temp=array();
                
                foreach ($model as $key => $value) {
                    $modelUpi=UpiTransaction::find()->where(['id' => $value->upi_transaction_id])->all();
                    
                    $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                    $modelProduct=Products::find()->where(['id' => $value->product_id])->all();
                    $modelUser=User::find()->where(['id' => $modelUpi[0]['user_id']])->all();
                
                    $appFromEmail="support@prezenty.in";
                    
                    $temp[$key]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                    $temp[$key]['date']=$model[0]->created_at;
                    $temp[$key]['amountR']=$modelUpi[0]['amount'];
                    $temp[$key]['amount']=$model[0]->amount;
                    $temp[$key]['state']=$modelUpi[0]->state;
                    $temp[$key]['id']='FOOD_'.$modelUpi[0]['id'];
                    $temp[$key]['type']='FOOD';
                    $temp[$key]['status']=$value->status;
                    $temp[$key]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                    $temp[$key]['name']=$modelUser[0]['name'];
                    $temp[$key]['email']=$modelUser[0]['email'];
                    $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                    $temp[$key]['gst']=$modelTax->option_value;
                    $temp[$key]['count']=count($model);
                    $temp[$key]['serviceCharge']=0;
                    $temp[$key]['taxAmount']=0;
                    $temp[$key]['invNo']=$model[0]->inv_no_id;
                    $temp[$key]['order_id']=$model[0]->order_id;
                    // $temp[$key]['image']=$modelProduct[0]['image_small'];
                $temp[0]['image'] = CustomHelper::getWoohooVoucherImageUrl($model[0]['product_id']);
               
                if($model[0]->status == 'COMPLETE'){
                    
                    $modelOrderDetail = OrderDetail::find()->where(['upi_transaction_id'=>$postData['id'],'order_type'=>'FOOD'])->all();
                    $temp[$key]['rece_name']=$modelOrderDetail[0]->recipient_name;
                    $temp[$key]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                    $temp[$key]['rece_email']=$modelOrderDetail[0]->recipient_email; 
                    $status='COMPLETE';
                    $temp[$key]['status']='COMPLETE';   
            
                } else {
                    
                    $temp[$key]['status']='FAILED';   
                    $status='FAILED';
                    
                } 
                    $temp[$key]['voucher']=$modelProduct[0]['name'];
            }
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                
                // return $mailData;
                
            } else{
                
                $model = OrderMaster::find()->where(['redeem_transaction_id'=>$postData['id'],'order_type'=>'REDEEM'])->all();
                
                $modelRedeem=Redeem::find()->where(['id' => $model[0]['redeem_transaction_id']])->all();
                $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                $temp[0]['date']=$model[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRedeem[0]['amount'];
                $temp[0]['state']=$modelRedeem[0]['state'];
                $temp[0]['type']='REDEEM';
                $temp[0]['id']='REDEEM_'.$modelRedeem[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                $temp[0]['serviceCharge']=0;
                $temp[0]['taxAmount']=0;
                $temp[0]['invNo']=$model[0]->inv_no_id;
                $temp[0]['voucher']=$modelProduct[0]['name'];
                //  $temp[0]['image']=$modelProduct[0]['image_small'];
                $temp[0]['image'] = CustomHelper::getWoohooVoucherImageUrl($model[0]['product_id']);
                $email=$modelUser[0]['email'];
                $temp[0]['order_id']=$model[0]->order_id;
                
                if($model[0]->status == 'COMPLETE'){
                
                $modelOrderDetail = OrderDetail::find()->where(['redeem_transaction_id'=>$postData['id'],'order_type'=>'REDEEM'])->all();    
                $temp[0]['rece_name']=$modelOrderDetail[0]->recipient_name;
                $temp[0]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                $temp[0]['rece_email']=$modelOrderDetail[0]->recipient_email;     
                $status='COMPLETE';
                $temp[0]['status']='COMPLETE';  
                 
                } else {
                    
                $temp[0]['status']='FAILED';   
                $status='FAILED';
                
                }
                
                $mailData['mailData']=$temp;
                
            } 

        // $email='albinsunny1996@gmail.com';
        


        


        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-summary' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Order Summary')->setTextBody('Invoice Details')->send();  
        
/*            $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/tax-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();                       */     
        
        if($status == 'COMPLETE'){
        
            $this->renderPartial('@common/mail/tax-invoice',$mailData);
            $mpdf=new mPDF();
            $mpdf->WriteHTML($this->renderPartial('@common/mail/tax-invoice',$mailData));
            
            $location= Yii::$app->basePath.'/web/pdf/';
            $path = $mpdf->Output($location.'TaxInvoice.pdf', \Mpdf\Output\Destination::FILE);
            $mail = Yii::$app->mailer->compose()->attach($location.'TaxInvoice.pdf')->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();
            
            if($email) {
            
            if($mailData['mailData'][0]['rece_email']==$email){
                
                $message ="Order Completed Successfully!";
                $payload=[];
                
                Yii::$app->notification->sendToOneUser(
                    $message,
                    $email,
                    null,
                    $message,
                    false,
                    $payload
                  );
                
            } else {
                
                $message ="Order Completed Successfully!";
                $payload=[];
            
                Yii::$app->notification->sendToOneUser(
                    $message,
                    $email,
                    null,
                    $message,
                    false,
                    $payload
                  );
                
                $message ="You Got a Gift From".$mailData['mailData'][0]['name'];
                $payload=[];
            
                Yii::$app->notification->sendToOneUser(
                    $message,
                    $mailData['mailData'][0]['rece_email'],
                    null,
                    $message,
                    false,
                    $payload
                  );                  
                
                
            }

            }            
        }
            $ret = [                    
                'statusCode' => 200,
                'message' => 'Success',
                'success' => true
            ];

        return $ret;         
         
    }    


    /*public function actionSendInvoiceByType(){
    
         $postData = Yii::$app->request->post();
         $mailData=[];
         $appFromEmail="support@prezenty.in";
         $email='';
         if($postData['order_type'] == 'BUY'){
            
            $model = OrderDetail::find()->where(['rzp_payment_id'=>$postData['id'],'order_type'=>'BUY'])->all();
            $modelRazor=RzpPayments::find()->where(['payment_id' => $model[0]->rzp_payment_id])->all();
            
            $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
            $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'--';
                $temp[0]['date']=$modelRazor[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRazor[0]['amount'];
                $temp[0]['id']='BUY_'.$modelRazor[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'--';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
              
                $amount= count($model)*$model[0]['amount'];
                $gst = $modelTax->option_value;
                
                $rzpCharge = $amount * .02;
                $rzpChargeTax = $rzpCharge * ($gst/100);
        
                $temp[0]['serviceCharge'] = $rzpCharge + $rzpChargeTax;
                //$temp[0]['gst']=$modelTax->option_value;
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                //print_r($mailData);exit;

         } else if($postData['order_type'] == 'FOOD') {
            
            $model = OrderDetail::find()->where(['upi_transaction_id'=>$postData['id'],'order_type'=>'FOOD'])->all();    

               $temp=array();
                
                foreach ($model as $key => $value) {
                
                
                    $modelUpi=UpiTransaction::find()->where(['id' => $value->upi_transaction_id])->all();
                    
                    $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                    $modelProduct=Products::find()->where(['id' => $value->product_id])->all();
                    $modelUser=User::find()->where(['id' => $modelUpi[0]['user_id']])->all();
                
                    $appFromEmail="support@prezenty.in";
                                           
                    
                    $temp[$key]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                    $temp[$key]['date']=$model[0]->created_at;
                    $temp[$key]['amountR']=$modelUpi[0]['amount'];
                    $temp[$key]['amount']=$model[0]->amount;
                    $temp[$key]['id']='FOOD_'.$modelUpi[0]['id'];
                    $temp[$key]['status']=$value->status;
                    $temp[$key]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                    $temp[$key]['name']=$modelUser[0]['name'];
                    $temp[$key]['email']=$modelUser[0]['email'];
                    $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                    $temp[$key]['gst']=$modelTax->option_value;
                    $temp[$key]['count']=count($model);
                    $temp[$key]['serviceCharge']=0;
                    //$temp[$key]['gst']=0;
                    
                    $temp[$key]['voucher']=$modelProduct[0]['name'];
            }
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                
                
            } else{
                
                $model = OrderDetail::find()->where(['redeem_transaction_id'=>$postData['id'],'order_type'=>'REDEEM'])->all(); 
                
                $modelRedeem=Redeem::find()->where(['id' => $model[0]['redeem_transaction_id']])->all();
                $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                $temp[0]['date']=$model[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRedeem[0]['amount'];
                $temp[0]['id']='REDEEM_'.$modelRedeem[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                $temp[0]['serviceCharge']=0;
                //$temp[0]['gst']=0;
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $email=$modelUser[0]['email'];
                
                $mailData['mailData']=$temp;
                
            } 

        //$email='albinsunny1996@gmail.com';

        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Order Summary')->setTextBody('Invoice Details')->send();            
            
            //$mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/tax-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();                            
        
        $this->renderPartial('@common/mail/tax-invoice',$mailData);
        $mpdf=new mPDF();
        $mpdf->WriteHTML($this->renderPartial('@common/mail/tax-invoice',$mailData));
        
        $location= Yii::$app->basePath.'/web/pdf/';
        $path = $mpdf->Output($location.'TaxInvoice.pdf', \Mpdf\Output\Destination::FILE);
        $mail = Yii::$app->mailer->compose()->attach($location.'TaxInvoice.pdf')->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();
        
            $ret = [                    
                'statusCode' => 200,
                'message' => 'Success',
                'success' => true
            ];

        return $ret;         
     
    }*/ 
    
    public function actionPdf(){
        
        //print_r(Yii::$app->basePath.'/pdf');exit;
        $mpdf=new mPDF();
        $mpdf->WriteHTML($this->renderPartial('@common/mail/tax-invoice',$mailData));
        
        $location= Yii::$app->basePath.'/web/pdf/';
        $path = $mpdf->Output($location.'MyPvDF.pdf', \Mpdf\Output\Destination::FILE);

        $appFromEmail="support@prezenty.in";
       // $email='albinsunny1996@gmail.com';
        //print_r($location.'MyPDF.pdf');exit;
        //$path=$mpdf->Output('F',Yii::$app->basePath.'/web/pdf/MyPDF.pdf');
        //print_r(Yii::$app->request->hostInfo.'/prezenty/api/web/pdf/MyPDF.pdf');exit;
        //$location='../web/pdf/MyPDF.pdf';
        //$location='https://www2.dnr.state.mi.us/Publications/PDFS/huntingwildlifehabitat/SGA/_DNR-WLD_help_with_phone_n_GPS_PDF_maps.pdf';
        $mail = Yii::$app->mailer->compose()->attach($location.'MyPvDF.pdf')->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();exit;
    }
    
    public function actionSendSms(){
         $v3='45';
         //$sndSms = CustomHelper::sendSmsCard('+919544855856','250',$v3='null','552');
         
        // $sndSms = CustomHelper::sendSmsRecipient('9544855856','250');
       //  $sndSms = CustomHelper::sendSmsSender('9544855856','250',$v3='tr','552','ff');
                                    
                                    $phoneSmsSend=8547324175;
                                    $amountR=5;
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
                             curl_close($curl);     
    }
    
    public function actionNoti(){
        
        $email='maneshvm01@gmail.com';
        if($email) {
          $message ="Hello Manesh";
         
/*        $payload = [
            'id' => 985,
            'sender_email' => 'albinkumpalamthanam@gmail.com',
            'date' => date("d-m-Y"),
            'time' => '12:00',
            'participant_id' => 985,
            'event_id' => 200,
            'event_title' => ''
        ];*/
        $payload=[];
          Yii::$app->notification->sendToOneUser(
            $message,
            $email,
            null,
            $message,
            false,
            $payload
          );
        }
        
    }
public function actionNotif(){
    
    print_r('Hacked !!!!!');exit;
    
}    
    
}