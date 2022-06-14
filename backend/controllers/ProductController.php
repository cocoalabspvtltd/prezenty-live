<?php

namespace backend\controllers;
use yii;

use api\components\Auth;
use backend\models\Product;
use backend\models\Products;
use backend\models\ProductOrder;
use yii\data\ActiveDataProvider;
use DateTime;
use Razorpay\Api\Api;
use yii\web\Controller;
use linslin\yii2\curl;
use common\components\CustomHelper;
use yii\helpers\Url;
use backend\models\OrderDetail;
use backend\models\OrderMaster;
use backend\models\User;
use backend\models\RzpPayments;
use backend\models\TaxSettings;
use backend\models\InvoiceNo;
use Mpdf\Mpdf;

class ProductController extends Controller
{
	public $modelClass = 'backend\models\Product';    

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

/*    public function actionIndex()
    {
        $searchModel = new ProductOrder();
        $dataProvider = $searchModel->search();
    
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }*/
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*')
                                                ->from('products as tb1')
                                                
            ]);
    
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }    
    public function actionOrder()
    {
        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*,tb2.*,tb1.created_at as created,tb1.id as ordId,tb1.status as statusOrd')
                                                ->from('order_detail as tb1')
                                                ->innerJoin('products as tb2','tb2.id = tb1.product_id')
                                                ->orderBy('tb1.id DESC')
            ]);
    
        return $this->render('order', [
            'dataProvider' => $dataProvider,
        ]);
    }     
    private function generateAuthKeysOrderDet($id){

        $return = array();
        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $return['dateAtClient']=$time->format(DateTime::ATOM);
        $secret='1a33c6118d1e0c74f7a012991d0e4e39';
        $url=WOOHOO_URL.'/rest/v3/order/'.$id.'/status';

        $string= '';

        $string='GET&'.urlencode($url);
/*        print_r($string);exit;
        $string= str_replace('+','%20',$string);*/

        $return['signature'] = hash_hmac('sha512', $string, $secret);        
         return $return;
    }
    private function generateAuthKeysOrderCardDet($id){

        $return = array();
        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $return['dateAtClient']=$time->format(DateTime::ATOM);
        $secret='1a33c6118d1e0c74f7a012991d0e4e39';

        $url=WOOHOO_URL."/rest/v3/order/".$id."/cards";

        $string= '';

        $string='GET&'.urlencode($url);
/*        print_r($string);exit;
        $string= str_replace('+','%20',$string);*/

        $return['signature'] = hash_hmac('sha512', $string, $secret);        
         return $return;
    }           
    public function actionView($id)
    {
        $curl = curl_init();
        //for getting auth code
        $ProductOrder = ProductOrder::findOne($id);        
        $ordID = $ProductOrder->order_razor_pay_id;           
        $keys=$this->generateAuthKeysOrderDet($ordID);
        $url=WOOHOO_URL;
        $auth = array(
            'clientId' => '88d7346c8674587bc95f8fbde2e33acd',
            'username' => 'prezenty.sandbox@woohoo.in',
            'password' => "woohoo@2021",
        );
        $payloadAuth = json_encode($auth);

        curl_setopt($curl, CURLOPT_URL,$url."/oauth2/verify");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$payloadAuth);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $authCode = curl_exec($curl);
         curl_close($curl);
         $curl = curl_init();  
        //for getting token
        $authCode=json_decode($authCode);
        $tokenKey = array(
            'clientId' => '88d7346c8674587bc95f8fbde2e33acd',
            'clientSecret' => '1a33c6118d1e0c74f7a012991d0e4e39',
            'authorizationCode' => $authCode->authorizationCode,
        );
        $payloadToken = json_encode($tokenKey);

        curl_setopt($curl, CURLOPT_URL,$url."/oauth2/token");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$payloadToken);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $token = curl_exec($curl);
        $token = json_decode($token);
        $authorization=$token->token;

        $body='';
        $dateAtClient=$keys['dateAtClient'];
        $signature=$keys['signature'];
        curl_close($curl);
        $curl = curl_init();  
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://sandbox.woohoo.in/rest/v3/order/".$ordID."/status",
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
            "dateAtClient: $dateAtClient",
            "signature: $signature",
            "Authorization: Bearer $authorization"
        ),
    ));

        $response = curl_exec($curl);
        curl_close($curl);                
        $urlReturn=json_decode($response);
        if(isset($urlReturn->status)){

             $ProductOrder = ProductOrder::findOne($id);
             $ProductOrder->woohoo_order_status=$urlReturn->status;
             $ProductOrder->save();

             $curl = curl_init();
        //for getting auth code
                  
        $keys=$this->generateAuthKeysOrderCardDet($ProductOrder->woohoo_order_id);
        $url=WOOHOO_URL;
        $auth = array(
            'clientId' => '88d7346c8674587bc95f8fbde2e33acd',
            'username' => 'prezenty.sandbox@woohoo.in',
            'password' => "woohoo@2021",
        );
        $payloadAuth = json_encode($auth);

        curl_setopt($curl, CURLOPT_URL,$url."/oauth2/verify");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$payloadAuth);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $authCode = curl_exec($curl);
         curl_close($curl);
         $curl = curl_init();  
        //for getting token
        $authCode=json_decode($authCode);
        $tokenKey = array(
            'clientId' => '88d7346c8674587bc95f8fbde2e33acd',
            'clientSecret' => '1a33c6118d1e0c74f7a012991d0e4e39',
            'authorizationCode' => $authCode->authorizationCode,
        );
        $payloadToken = json_encode($tokenKey);

        curl_setopt($curl, CURLOPT_URL,$url."/oauth2/token");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$payloadToken);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $token = curl_exec($curl);
        $token = json_decode($token);
        $authorization=$token->token;

        $body='';
        $dateAtClient=$keys['dateAtClient'];
        $signature=$keys['signature'];
        curl_close($curl);
        $curl = curl_init();  
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://sandbox.woohoo.in/rest/v3/order/".$ProductOrder->woohoo_order_id."/cards",
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
            "dateAtClient: $dateAtClient",
            "signature: $signature",
            "Authorization: Bearer $authorization"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);                 
        $urlReturn=json_decode($response);
         $ProductOrder = ProductOrder::findOne($id); 
             $ProductOrder->woohoo_card_num=$urlReturn->cards[0]->cardNumber;
             $ProductOrder->save();
        return $this->render('view', [
            'dataProvider' => $urlReturn,
        ]);

        } else {

            $urlReturn='';
            return $this->render('view', [
                'dataProvider' => $urlReturn,
            ]);
        }

    }
    public function actionIsFood($id){

        $Product = Products::find()->where(['id'=>$id])->one();
        $Product->voucher_type=1;
        $Product->save(false);
        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*')
                                                ->from('products as tb1')
                                                
            ]);
    
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);        

    }
    public function actionIsGift($id){

        $Product = Products::find()->where(['id'=>$id])->one();
        $Product->voucher_type=2;
        $Product->save(false);
        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*')
                                                ->from('products as tb1')
                                                
            ]);
    
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);        

    }  
    
    public function actionFailedOrders(){

        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*,tb2.*,tb1.created_at as created,tb1.id as oid,tb2.name product,tb3.name as order_by,tb3.email as user_email,tb3.phone_number as phone_number,tb1.status as stas')
                                                ->from('order_master as tb1')
                                                ->innerJoin('products as tb2','tb2.id = tb1.product_id')
                                                ->innerJoin('user as tb3','tb3.id = tb1.user_id')
                                                ->where(['tb1.status' => 'FAILED'])
                                                ->orwhere(['tb1.status'=>'PROCESSING'])
                                                ->orwhere(['tb1.status'=>'ERROR'])
                                                ->orderBy('tb1.id DESC')
            ]);
    
        return $this->render('order-failed', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionReRequestInit($id){

    $orderModel = OrderMaster::find()->where(['id'=>$id])->one();
    $order_id=$orderModel->order_id;
   print_r($order_id);exit;
        try {
            if(isset($order_id))
            {   
                $Product = OrderDetail::find()->where(['id'=>$order_id])->one();
                $order_id=$Product->order_id;
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/order/'.$order_id.'/cards';
                $getQueryParamData =  Yii::$app->request->get();
                $requestHeader = Yii::$app->request->headers;
                
                $verify = CustomHelper::getToken();
                
                $acceptableParams = ['offset','limit'];

                $filterParamData = array();
                forEach($getQueryParamData as $key => $val)
                {
                    if(in_array($key,$acceptableParams))
                    {
                        $filterParamData[$key]=$val;
                    }
                }
      
                $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'GET');

                if(count($filterParamData) > 0){
                    ksort($filterParamData);
                    $qString = '?';
                    forEach($filterParamData as $key => $val){
                        $qString .= $key.'='.$val.'&';
                    }
                    $baseUrl = $baseUrl.rtrim($qString,'&');
                }
                
                $dateAtClient = CustomHelper::getDateAtClient();

                if($signature && $signature != null)
                {
                    $curl = new curl\Curl();
                    $response = $curl->setHeaders([
                            'Content-Type' => 'application/json',
                            'signature' => $signature,
                            'dateAtClient' => $dateAtClient,
                            'authorization'=> 'Bearer '.$verify->token
                        ])->get($baseUrl,true);
                        
                        $data = (array) json_decode($response,true);
                        //print_r($data);exit;
                        if(isset($data['cards'])){ 

                            $Product->status = 'COMPLETE';
                            $Product->card_number = $data['cards'][0]['cardNumber'];
                            $Product->card_pin = $data['cards'][0]['cardPin'];
                            $Product->activation_code = $data['cards'][0]['activationCode'];
                            $Product->barcode = $data['cards'][0]['barcode'];
                            $Product->activation_url =  $data['cards'][0]['activationUrl'];
                            $Product->amount = $data['cards'][0]['amount'];
                            $Product->validity = $data['cards'][0]['validity'];
                            $Product->issuance_date =$data['cards'][0]['issuanceDate'];
                            $Product->card_id = $data['cards'][0]['cardId'];
                            $Product->save(false);
                            
                        }                         
                }
           
            }
        } catch (ErrorException $e) {
            return $e;
        } 
        

        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*,tb2.*,tb1.created_at as created,tb1.id as oid,tb2.name product,tb3.name as order_by,tb3.email as user_email,tb3.phone_number as phone_number,tb1.status as stas')
                                                ->from('order_master as tb1')
                                                ->innerJoin('products as tb2','tb2.id = tb1.product_id')
                                                ->innerJoin('user as tb3','tb3.id = tb1.user_id')
                                                ->where(['tb1.status' => 'FAILED'])
                                                ->orwhere(['tb1.status'=>'PROCESSING'])
                                                ->orwhere(['tb1.status'=>'ERROR'])
                                                ->orderBy('tb1.id DESC')
            ]);
    
        return $this->render('order-failed', [
            'dataProvider' => $dataProvider,
        ]);       
    }

    public function actionReCreateInit($id = null){ 
        
	        
	        $Product =OrderMaster::find()->where(['id'=>$id])->one();
	        $OrderMasterId=$id;
	        $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders';
            $verify = CustomHelper::getToken();
        
            
			//$orderBody = json_encode($Product->request_body);
			$orderBody=json_decode($Product->request_body,true);
			$datePrezenty = new DateTime();
	        $orderBody['refno']='prezenty_'.$datePrezenty->getTimestamp();
			
			//$orderBody = json_decode($Product->request_body);
            $filterParamData = array();
            $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'POST',$orderBody); 
            $dateAtClient = CustomHelper::getDateAtClient();
            
            $OrderMasterCheckInv=OrderMaster::find()->where(['upi_transaction_id'=>$Product->upi_transaction_id])->
                orWhere(['rzp_payment_id'=>$Product->rzp_payment_id])->one();
            
            $InvMasterId='';
            
            if(isset($OrderMasterCheckInv->upi_transaction_id))  {
            
            $InvMasterId=$OrderMasterCheckInv->inv_no_id;
            
            } else {
            
            $model=new InvoiceNo();
            $model->save();
            $InvMasterId='prznty_'.$model->id;                
            }            
            
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
            
            if($Product->order_type == 'REDEEM'){ 
                
                $modelRedeem = Redeem::find()->where(['id'=>$Product->redeem_transaction_id])->one();
                $modelRedeem-> status=$data['status'];
                $modelRedeem-> save(false); 
                    
             }
            $orderBody = json_decode($Product->request_body);
            
            if(isset($data['code'])){
                    
/*                        $order = new OrderDetail();
                        $order->sku = $orderBody->products[0]->sku;
                        $order->status = 'FAILED';
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$Product->user_id;
                        $order->product_id=$Product->product_id;
                        $order->order_type=$Product->order_type;
                        $order->upi_transaction_id=$Product->upi_transaction_id;
                        $order->redeem_transaction_id=$Product->redeem_transaction_id;
                        $order->rzp_payment_id=$Product->rzp_payment_id; 
                        $order->status_response=json_encode($data);
                        $order->save(false);*/
                        
                        $Product=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                         if(isset($data['message'])){
                            
                            $Product->message=$data['message'];
                            
                        } else {
                            
                            $Product->message='FAILED';
                        }
                        $Product->status='FAILED';
                        $Product->save(false);
                    
                } else {
                    
                if($data['status'] == 'COMPLETE') {
                    
                    $nameSmsRec='';
                    $phoneSmsRec='';
                    $phoneSmsSend='';
                    $nameSmsSend='';
                    $emailSmsRec='';
                    
                    foreach ($data['cards'] as $key => $value) {
                        $order = new OrderDetail();
                        
                        $order->sku = $orderBody->products[0]->sku;
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
                        $order->recipient_country = $orderBody->address->country;
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$Product->user_id;
                        $order->product_id=$Product->product_id;
                        $order->order_type=$Product->order_type;
                        $order->upi_transaction_id=$Product->upi_transaction_id;
                        $order->redeem_transaction_id=$Product->redeem_transaction_id;
                        $order->rzp_payment_id=$Product->rzp_payment_id;  
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
                            
                            $v3=(isset($value['sku'])) ? $value['sku'] : "empty";
    
                            $v3 = str_replace(' ', '%20', $v3);
    
                            $sndSms = CustomHelper::sendSmsCard($phoneSmsRec,$v1,$v2,$v3);
                            } catch (ErrorException $e) {
                        }
                       
                        }
                        
                        $Product=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                        $Product->order_id=$data['orderId'];
                        $Product->status=$data['status'];
                        $Product->save(false);
                     
                    // send mail
                    try{
                       
                        $query = "select json_data from product_details where product_id = ".$Product->product_id." limit 1";
                        $command = Yii::$app->db->createCommand($query);
                        $result = $command->queryAll(); 
                        $UserD=User::find()->where(['id'=> $Product->user_id])->one();
                        $phoneSmsSend=$UserD->phone_number;
                        $nameSmsSend=$UserD->name;
                        $mailData[0]['content'] = json_decode($result[0]['json_data']) -> tnc -> content;
                        $mailData[0]['logo'] = Url::base(true).'/ic_logo.png';
                         $mailData[0]['giftedBy']=$UserD->name;
                        $mailData[0]['mob']=$phoneSmsRec;
                        $mailData[0]['email']=$emailSmsRec;
                         $mailData[0]['invNo']=$InvMasterId;
                        $mailData[0]['id']=$InvMasterId;
                        $mailData[0]['voucherImg'] = CustomHelper::getWoohooVoucherImageUrl($Product->product_id);
                        
                        
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
                            $mailData[$key]['currency_symbol'] = $data['currency']['symbol'];
                            $mailData[$key]['price'] = $value['amount'];
                            $mailData[$key]['amount'] = $value['amount'];            
                            $sku = $value['sku'];                    
                                            
                            $mailData[$key]['image'] = $data['products'][$sku]['images']['mobile'];
                            $mailData[$key]['content'] = '';
                        }                
            
                       $testArray['mailData']=$mailData;
                        
                        $appFromEmail = 'support@prezenty.in';
                        $toEmail = $data['cards'][0]['recipientDetails']['email'];
                        
                        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/card-details' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('You got a gift from '.$mailData[0]['giftedBy'])->setTextBody('Order Details')->send();                       
                         
                
                
                $Product =OrderMaster::find()->where(['id'=>$OrderMasterId])->one();                        
                $mailData=[];
                $appFromEmail="support@prezenty.in";
                $email='';
                
                $sndSms = CustomHelper::sendSmsRecipient($phoneSmsSend,$nameSmsRec);
                    
                if($Product->order_type == 'BUY'){
            
                        $model = OrderDetail::find()->where(['rzp_payment_id'=>$Product->rzp_payment_id,'order_type'=>'BUY'])->all();
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
                        $temp[0]['id']='BUY_'.$modelRazor[0]['payment_id'];
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
                        
                        $temp[0]['serviceCharge']=round($rzpCharge,2);
                        $temp[0]['taxAmount']=round($rzpChargeTax,2);
                        
                        $temp[0]['voucher']=$modelProduct[0]['name'];
                        $mailData['mailData']=$temp;
                        $email=$modelUser[0]['email'];
                        
                        $sndSms = $this->actionSendInvoiceByType($Product->rzp_payment_id,'BUY');
                        
                //print_r($mailData);exit;

                } else if($Product->order_type == 'FOOD') {
            
                        $model = OrderDetail::find()->where(['upi_transaction_id'=>$Product->upi_transaction_id,'order_type'=>'FOOD'])->all();    
                        
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
                        $temp[$key]['taxAmount']=0;
                        
                        $temp[$key]['voucher']=$modelProduct[0]['name'];
                        }
                        $mailData['mailData']=$temp;
                        $email=$modelUser[0]['email'];
                        
                         $sndSms = $this->actionSendInvoiceByType($Product->upi_transaction_id,'FOOD');
                
                // return $mailData;
                } else{
                
                    $model = OrderDetail::find()->where(['redeem_transaction_id'=>$Product->redeem_transaction_id,'order_type'=>'REDEEM'])->all(); 
                    
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
                    $temp[0]['taxAmount']=0;
                    
                    $temp[0]['voucher']=$modelProduct[0]['name'];
                    $email=$modelUser[0]['email'];
                    
                    $mailData['mailData']=$temp;
                    
                    $sndSms = $this->actionSendInvoiceByType($Product->redeem_transaction_id,'REDEEM');
                
                 } 

                /*
                    SMS -Sender 2
                */                                    
                    
                    $refSmsNo='';
                    if(isset($Product->upi_transaction_id)){
                        
                        $refSmsNo=$Product->order_type.'_'.$Product->upi_transaction_id;
                        
                    } else if(isset($Product->redeem_transaction_id)) {
                        
                        $refSmsNo=$Product->order_type.'_'.$Product->redeem_transaction_id;
                        
                    } else {
                        
                        $refSmsNo=$Product->order_type.'_'.$Product->rzp_payment_id;
                    }
                    
                     $dateSms=date("d-m-Y");
                    
                    $sndSms = CustomHelper::sendSmsSender($phoneSmsSend,$nameSmsSend,$InvMasterId,$refSmsNo,$dateSms);
                    
                    // $email='maneshvmohanan@gmail.com';
                    //$mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-complete' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Order Summary')->setTextBody('Invoice Details')->send();                          
                         
                    } catch (ErrorException $e) {
                    }
                    
                } else {
                    if(isset($data['orderId'])){
                        $order = new OrderDetail();
                        $order->sku = $orderBody->products[0]->sku;
                        $order->order_id = $data['orderId'];
                        $order->reference_number = $data['refno'];
                        $order->status = $data['status'];
                        $order->amount = $orderBody->payments->amount;
                        $order->recipient_name = $orderBody->address->firstname;
                        $order->recipient_email = $orderBody->address->email;
                        $order->recipient_mobile = $orderBody->address->telephone;
                        $order->recipient_country = $orderBody->address->country;
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$orderBody->user_id;
                        $order->product_id=$orderBody->product_id;
                        $order->status_response=json_encode($data);
                        $order->order_type=$orderBody->order_type;
                        $order->upi_transaction_id=$orderBody->upi_transaction_id;
                        $order->redeem_transaction_id=$orderBody->redeem_transaction_id;
                        $order->rzp_payment_id=$orderBody->rzp_payment_id;                        
                        $order->save(false);
                        
                        $Product=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                        $Product->order_id=$data['orderId'];
                        $Product->status=$data['status'];
                        $Product->save(false);
                        
                    }else{
                                
                        $order = new OrderDetail();
                        $order->sku = $orderBody->products[0]->sku;
                        $order->status = 'ERROR';
                        $order->status_response=json_encode($data);
                        $order->request_body=json_encode($orderBody);
                        $order->user_id=$Product->user_id;
                        $order->product_id=$Product->product_id;
                        //$order->status_response=json_encode($data);
                        $order->order_type=$Product->order_type;
                        $order->upi_transaction_id=$Product->upi_transaction_id;
                        $order->redeem_transaction_id=$Product->redeem_transaction_id;
                        $order->rzp_payment_id=$Product->rzp_payment_id;    
                        
                        $order->save(false);
                        
                        $Product=OrderMaster::find()->where(['id'=>$OrderMasterId])->one();
                        $Product->order_id=$data['orderId'];
                        $Product->status=$data['status'];
                        $Product->save(false);                        
                
                        }
                    } 
                }
        
        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*,tb2.*,tb1.created_at as created,tb1.id as oid,tb2.name product,tb3.name as order_by,tb3.email as user_email,tb3.phone_number as phone_number,tb1.status as stas')
                                                ->from('order_master as tb1')
                                                ->innerJoin('products as tb2','tb2.id = tb1.product_id')
                                                ->innerJoin('user as tb3','tb3.id = tb1.user_id')
                                                ->where(['tb1.status' => 'FAILED'])
                                                ->orwhere(['tb1.status'=>'PROCESSING'])
                                                ->orwhere(['tb1.status'=>'ERROR'])
                                                ->orderBy('tb1.id DESC')
            ]);
    
        return $this->render('order-failed', [
            'dataProvider' => $dataProvider,
        ]);                   
            
    }   
    
    public  function actionSendInvoiceByType($id,$order_type){
    
         //$postData = Yii::$app->request->post();
         $mailData=[];
         $appFromEmail="support@prezenty.in";
         $email='';
         if($order_type == 'BUY'){
            
            $model = OrderMaster::find()->where(['rzp_payment_id'=>$id,'order_type'=>'BUY'])->all();
            
            $modelOrderDetail = OrderDetail::find()->where(['rzp_payment_id'=>$id,'order_type'=>'BUY'])->all();
            
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
                $temp[0]['rece_name']=$modelOrderDetail[0]->recipient_name;
                $temp[0]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                $temp[0]['rece_email']=$modelOrderDetail[0]->recipient_email;
                
            
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

         } else if($order_type == 'FOOD') {
            
            $model = OrderMaster::find()->where(['upi_transaction_id'=>$id,'order_type'=>'FOOD'])->all();
            $modelOrderDetail = OrderDetail::find()->where(['upi_transaction_id'=>$id,'order_type'=>'FOOD'])->all();

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
                    $temp[$key]['image']=$modelProduct[0]['image_small'];
                    
                    $temp[$key]['rece_name']=$modelOrderDetail[0]->recipient_name;
                    $temp[$key]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                    $temp[$key]['rece_email']=$modelOrderDetail[0]->recipient_email; 
                    
                    $temp[$key]['voucher']=$modelProduct[0]['name'];
            }
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                
                // return $mailData;
                
            } else{
                
                $model = OrderMaster::find()->where(['redeem_transaction_id'=>$id,'order_type'=>'REDEEM'])->all();
                
                 $modelOrderDetail = OrderDetail::find()->where(['redeem_transaction_id'=>$id,'order_type'=>'REDEEM'])->all();
                
                $modelRedeem=Redeem::find()->where(['id' => $model[0]['redeem_transaction_id']])->all();
                $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                $temp[0]['date']=$model[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRedeem[0]['amount'];
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
                 $temp[0]['image']=$modelProduct[0]['image_small'];
                $email=$modelUser[0]['email'];
                $temp[0]['order_id']=$model[0]->order_id;
                $temp[0]['rece_name']=$modelOrderDetail[0]->recipient_name;
                $temp[0]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                $temp[0]['rece_email']=$modelOrderDetail[0]->recipient_email;                 
                $mailData['mailData']=$temp;
                
            } 

        // $email='albinsunny1996@gmail.com';

        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-summary' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Order Summary')->setTextBody('Invoice Details')->send();  
        
/*            $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/tax-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();                       */     
        
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
    
     public  function actionReSendCardDet($id){
         
         if(Yii::$app->request->post()){
         $post=Yii::$app->request->post();
            
            $id=$post['OrderDetail']['id'];
            $toEmail=$post['OrderDetail']['recipient_email'];
                        $modelOrderDetail = OrderDetail::find()->where(['id'=>$id])->one();

                        $query = "select json_data from product_details where product_id = ".$modelOrderDetail->product_id." limit 1";
                        $command = Yii::$app->db->createCommand($query);
                        $result = $command->queryAll(); 
                        $UserD=User::find()->where(['id'=> $modelOrderDetail->user_id])->one();
                        $phoneSmsSend=$UserD->phone_number;
                        $nameSmsSend=$UserD->name;
                        $mailData[0]['content'] = json_decode($result[0]['json_data']) -> tnc -> content;
                        $mailData[0]['logo'] = Url::base(true).'/ic_logo.png';
                         $mailData[0]['giftedBy']=$UserD->name;
                        $mailData[0]['mob']=$modelOrderDetail->recipient_mobile;
                        $mailData[0]['email']=$modelOrderDetail->recipient_email;
                        $mailData[0]['invNo']=$modelOrderDetail->inv_no_id;
                        $mailData[0]['id']=$modelOrderDetail->inv_no_id;
                        $mailData[0]['voucherImg'] = CustomHelper::getWoohooVoucherImageUrl($modelOrderDetail->product_id);
                        
                        $data = (array) json_decode($modelOrderDetail->status_response,true);
                        
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
                            $mailData[$key]['currency_symbol'] = $data['currency']['symbol'];
                            $mailData[$key]['price'] = $value['amount'];
                            $mailData[$key]['amount'] = $value['amount'];            
                            $sku = $value['sku'];                    
                                            
                            $mailData[$key]['image'] = $data['products'][$sku]['images']['mobile'];
                            $mailData[$key]['content'] = '';
                        }                
            
                       $testArray['mailData']=$mailData;
                        
                        $appFromEmail = 'support@prezenty.in';
                        /*$toEmail = 'albinsunny1996@gmail.com';*/
                        
                        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/card-details' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('You got a gift from '.$mailData[0]['giftedBy'])->setTextBody('Order Details')->send();                       
                        yii::$app->session->setFlash('success','Sended Successfully');
         
         }
         $modelOrderDetail = OrderDetail::find()->where(['id'=>$id])->one();

                 return $this->render('card-resend', [
            'model' => $modelOrderDetail,
        ]); 
         
     }

    public  function actionProductSync($id=null){     
        
        $id=330;
        try {

            if(isset($id))
            {
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/categories/'.(int)$id.'/products';
                $getQueryParamData = Yii::$app->request->get();
                $requestHeader = Yii::$app->request->headers;

                if(!isset($requestHeader['authorization']))
                {
                    $code = 401;
                    Yii::$app->response->statusCode = 401;
                    $message = "Authorization token is not valid.";
                    $result = [
                        'result'     => [
                            'authorization' => 'Invalid'
                        ],
                        'statusCode' => $code,
                        'message'    => $message
                    ];
      
                    return $result;
                }

                $acceptableParams = ['offset','limit'];

                $filterParamData = array();
                forEach($getQueryParamData as $key => $val)
                {
                    if(in_array($key,$acceptableParams))
                    {
                        $filterParamData[$key]=$val;
                    }
                }
                $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'GET');
                
                if(count($filterParamData) > 0){
                    ksort($filterParamData);
                    $qString = '?';
                    forEach($filterParamData as $key => $val){
                        $qString .= $key.'='.$val.'&';
                    }
                    $baseUrl = $baseUrl.rtrim($qString,'&');
                }

                $dateAtClient = CustomHelper::getDateAtClient();
                $verify = CustomHelper::getToken();

                if($signature && $signature != null)
                {
                    $curl = new curl\Curl();
                    $response = $curl->setHeaders([
                            'Content-Type' => 'application/json',
                            'signature' => $signature,
                            'dateAtClient' => $dateAtClient,
                            'authorization'=> 'Bearer '.$verify->token //$requestHeader['authorization']
                        ])->get($baseUrl,true);

                    $data = json_decode($response);

                    if($data){
                        if(isset($data->code)){
                       $ret = [                    
                                'statusCode' => 200,
                                'data' => $data,
                                'message' => 'Token Invalid',
                                'success' => false
                            ];

                        return $ret;                               
                        }
                        if( sizeof($data->products) > 0 ){
                            foreach($data->products as $products){
                                $pdata = Products::find()->where('sku = "'.$products->sku. '" AND category_id ='.$data->id)->all();  
                                
                                if(sizeof($pdata) == 0){
                                    $product = new Products();
                                    if(isset($data->id)){
                                    $product->category_id  = $data->id;
                                    $product->sku = $products->sku;
                                    $product->name = $products->name;
                                    $product->currency_code = $products->currency->code;
                                    $product->currency_symbol = $products->currency->symbol;
                                    $product->currency_numeric_code = $products->currency->numericCode;
                                    $product->url = $products->url;
                                    $product->min_price = $products->minPrice;
                                    $product->max_price = $products->maxPrice;
                                    $product->image_thumbnail = $products->images->thumbnail;
                                    $product->image_mobile = $products->images->mobile;
                                    $product->image_base = $products->images->base;
                                    $product->image_small = $products->images->small;
                                    $product->createdAt = $products->createdAt;
                                    $product->updatedAt = $products->updatedAt;
                                    $product->created_at = date("Y-m-d H:i:s", time());
                                    $product->save();
                                    }
                                }
                            }
                        }
                    }
                      
                      $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                               ->select('tb1.*')
                                                ->from('products as tb1')
                                                
            ]);
            
         yii::$app->session->setFlash('success','Sync Successfully');
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
                                       
                }
            }
            else
            {
                $dataProvider = new ActiveDataProvider([
                'query' => (new \yii\db\Query())
                       ->select('tb1.*')
                        ->from('products as tb1')
                        
                ]);
                 yii::$app->session->setFlash('error','Internal Server Error');
                return $this->render('index', [
                    'dataProvider' => $dataProvider,
                ]);
            }
            
        } catch (ErrorException $e) {
            
            yii::$app->session->setFlash('error','Internal Server Error');
        }        
        
    }
     
         public  function actionOffer($id=null){    
             
             if(Yii::$app->request->post()){
                $post=Yii::$app->request->post();
                
                $modelProducts = Products::find()->where(['id'=>$id])->one();
                $modelProducts->offers=$post['Products']['offers'];
                $modelProducts->save(false);
                
                
             } 
             
              $modelProducts = Products::find()->where(['id'=>$id])->one();

                return $this->render('product-offer', [
                    'modelProducts' => $modelProducts,
                ]);
         }
}
