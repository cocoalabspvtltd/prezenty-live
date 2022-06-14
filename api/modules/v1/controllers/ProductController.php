<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use backend\models\Product;
use backend\models\ProductOrder;
use yii\data\ActiveDataProvider;
use DateTime;
use Razorpay\Api\Api;

class ProductController extends ActiveController
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

    public function actionList()
    {
        header('Access-Control-Allow-Origin: *');        
        $get = Yii::$app->request->get();
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;        
        $query = Product::find()->select(['pk_int_id', 'vchr_product_name','p_sku_id','p_image'])->where(['p_status'=>1]);	
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $baseUrl = Yii::$app->params['base_path_products_images'];	
       	$dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
		
		$ret = [
            'baseUrl' => $baseUrl,
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

    public function actionDescription($id)
    {
        $model = Product::find()->select(['pk_int_id', 'vchr_product_name','p_sku_id','p_price_min','p_price_max','p_image','vchr_product_desc','p_terms_and_conditions'])->where(['pk_int_id' => $id])->One();
        $baseUrl = Yii::$app->params['base_path_products_images'];  
        $ret = [
                    'baseUrl' => $baseUrl,
                    'statusCode' => 200,
                    'data' => $model,
                    'message' => 'Listed Successfully',
                    'success' => true
                ];

        return $ret;        
    }

/*    public function actionTermsAndConditions($id)
    {
        $model = Product::find()->select(['pk_int_id', 'vchr_product_name','p_sku_id','p_price_min','p_price_max','p_image','p_terms_and_conditions'])->where(['pk_int_id' => $id])->One();
        $ret = [
                    
                    'statusCode' => 200,
                    'data' => $model,
                    'message' => 'Listed Successfully',
                    'success' => true
                ];

        return $ret;        
    }    
*/  
    private function generateAuthKeys($data){

        $return = array();
        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $return['dateAtClient']=$time->format(DateTime::ATOM);
        $secret='1a33c6118d1e0c74f7a012991d0e4e39';
        $url=WOOHOO_URL.'/rest/v3/orders';

        $string= json_encode($data);

        $string='POST&'.urlencode($url).'&'.urlencode($string);
        $string= str_replace('+','%20',$string);

        $return['signature'] = hash_hmac('sha512', $string, $secret);        

         return $return;
    }

    private function generateAuthKeysOrderDet($id){

        $return = array();
        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $return['dateAtClient']=$time->format(DateTime::ATOM);
        $secret='1a33c6118d1e0c74f7a012991d0e4e39';
        $url=WOOHOO_URL.'/rest/v3/order/'.$id.'/cards';

        $string= '';

        $string='GET&'.urlencode($url);
/*        print_r($string);exit;
        $string= str_replace('+','%20',$string);*/

        $return['signature'] = hash_hmac('sha512', $string, $secret);        
         return $return;
    }    

    public function actionPlaceOrder(){

        $post = Yii::$app->request->post();
        
        $notes = $post['payload']['payment']['entity'];
         if($notes){

            $orderId=$post['payload']['payment']['entity']['notes']['orderId'];
            $productOrderId=$post['payload']['payment']['entity']['notes']['productOrderId'];

            $model = ProductOrder::find()->where(['pk_order_id' => $productOrderId])->One();
            $buildArray=array();
            $mobile='';

            if($model['vchr_user_mobile'] !=''){
                    
                    if(str_starts_with($model['vchr_user_mobile'],'+')){

                        $mobile=$model['vchr_user_mobile'];

                    } else {

                        $mobile='+91'.$model['vchr_user_mobile'];

                    }
                    //$mobile = ;

            } else {

                    $mobile = $post['payload']['payment']['entity']['contact'];

            }
            $buildArray['address']=array(
                'billToThis' => true,
                'country' => "IN",
                'email' => $model['vchr_user_email'],
                'firstname' => $model['vchr_user_name'],
                'telephone' =>$mobile,
            );    

      
            /*$buildArray['address']['billToThis']=true;
            $buildArray['address']['country']="IN";
            $buildArray['address']['email']=$model['vchr_user_email'];
            $buildArray['address']['firstname']=$model['vchr_user_name'];
            $buildArray['address']['telephone']=$model['vchr_user_mobile'];*/

            /*$buildArray['billing']['country']="IN";
            $buildArray['billing']['email']=$model['vchr_user_email'];
            $buildArray['billing']['firstname']=$model['vchr_user_name'];
            $buildArray['billing']['telephone']=$model['vchr_user_mobile'];*/
             $buildArray['billing']=array(
                'country' => "IN",
                'email' => $model['vchr_user_email'],
                'firstname' => $model['vchr_user_name'],
                'telephone' =>$mobile,
            );    

            $buildArray['deliveryMode'] ="API";
            $buildArray['payments'][]=array('amount' => $post['payload']['payment']['entity']['amount'],'code' =>'svc');


            //$buildArray['payments']['code']="svc";
            $buildArray['products'][]=array(
                'currency' => 356,
                'giftMessage' => '',
                'price' => $post['payload']['payment']['entity']['amount'],
                'qty' => 1,
                'sku' => 'CNPIN',
            );

/*            $buildArray['products']['currency']=365;
            $buildArray['products']['giftMessage']="";
            $buildArray['products']['price']=$post['payload']['payment']['entity']['notes']['amount'];
            $buildArray['products']['qty']=1;
            $buildArray['products']['sku']="CNPIN";*/

            $buildArray['refno']=$orderId;
            $buildArray['syncOnly']=false;   
            $keys=$this->generateAuthKeys($buildArray);
        $url=WOOHOO_URL;    
        $curl = curl_init();
        //for getting auth code

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

        $body=json_encode($buildArray);
        $dateAtClient=$keys['dateAtClient'];
        $signature=$keys['signature'];
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://sandbox.woohoo.in/rest/v3/orders',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$body,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Accept: */*",
            "dateAtClient: $dateAtClient",
            "signature: $signature",
            "Authorization: Bearer $authorization"
        ),
    ));

    }       
            $response = curl_exec($curl);
            curl_close($curl);                 
           $urlReturn=json_decode($response);

           if(isset($urlReturn->orderId)){

            $ProductOrder = ProductOrder::findOne($productOrderId);   
            $ProductOrder->woohoo_order_id=$urlReturn->orderId;
            $ProductOrder->vchr_user_mobile=$mobile;
            $ProductOrder->woohoo_order_status=$urlReturn->status;
            $ProductOrder->woohoo_balance=$urlReturn->payments[0]->balance;
            $ProductOrder->save();

            $ret = [
                    'woohoOrderId' => $urlReturn->orderId,
                    'productOrderId' => $productOrderId,
                    'statusCode' => 200,
                    'message' => 'Created Successfully',
                    'success' => true,
            ];

           } else {

                $ret = [
                    'orderId' => null,
                    'productOrderId' => null,
                    'statusCode' => 200,
                    'message' => $urlReturn->message,
                    'success' => false,
                ];   
                
           }
            //$notes = $post['payload']['payment']['entity']['notes'];

            return $ret;

    }

    public function actionCreateOrder()
    {   
        $get = Yii::$app->request->get();
        $ret = [];
        $statusCode = 200;
        $success = false;
        $msg = '';

        $product_id = isset($get['product_id'])?$get['product_id']:'';
        $amount = isset($get['amount'])?$get['amount']:'';
        $mobile = isset($get['mobile'])?$get['mobile']:'';
        $email = isset($get['email'])?$get['email']:'';
        $userId = isset($get['userId'])?$get['userId']:'';
        $userName = isset($get['vchr_user_name'])?$get['vchr_user_name']:'';

        if(!$amount || !$product_id){

            $msg = "Amount and Product cannot be blank.";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }

        $api_key = KEY_ID;
        $api_secret = KEY_SECRET;
        $api = new Api($api_key, $api_secret);
        $amt = $amount * 100;
        try {

            $order = $api->order->create(
            array(
                'receipt' => $product_id,
                'amount' => $amt,
                'currency' => 'INR'
                )
            );

            $ProductOrder = new ProductOrder;    
            $ProductOrder->vchr_user_email=$email;
            $ProductOrder->vchr_user_mobile=$mobile;
            $ProductOrder->order_amount=$amount;
            $ProductOrder->order_razor_pay_id=$order->id;
            $ProductOrder->fk_int_product_id=$product_id;
            $ProductOrder->fk_int_user_id=$userId;
            $ProductOrder->vchr_user_name=$userName;
            $ProductOrder->save();


        $ret = [
                'orderId' => $order->id,
                'productOrderId' => $ProductOrder->pk_order_id,
                'convertedAmount' => $amt,
                'statusCode' => 200,
                'message' => 'Created Successfully',
                'success' => true,
            ];            


        } catch (\Exception $exception){

        $ret = [
            'orderId' => null,
            'productOrderId' => null,
            'convertedAmount' => $amt,
            'statusCode' => 200,
            'message' => $exception->getMessage(),
            'success' => false,
        ];   

        }                
        
        return $ret;


    }

    public function actionGetOrders($id)
    {
        $model = (new \yii\db\Query())
        ->from('event_product_order as tb1')
        ->innerJoin('event_product_master as tb2', 'tb1.fk_int_product_id = tb2.pk_int_id')
        ->where(['tb1.fk_int_user_id' => $id])
        ->orderBy([
            'tb1.pk_order_id' => SORT_DESC      
        ])->all();
        $baseUrl = Yii::$app->params['base_path_products_images'];
        $ret = [
            'data' => $model,  
            'baseUrl' => $baseUrl,         
            'statusCode' => 200,
            'message' => 'Listed Successfully',
            'success' => true,
        ];

        return $ret;
    }

    public function actionGetOrderStatus()
    {
        $curl = curl_init();
        //for getting auth code
        $post = Yii::$app->request->post();            
        $keys=$this->generateAuthKeysOrderDet($post['orderId']);
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
          CURLOPT_URL => "https://sandbox.woohoo.in/rest/v3/order/".$post['orderId']."/cards",
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
        $ret = [
            'data' => $urlReturn,              
            'statusCode' => 200,
            'message' => 'Listed Successfully',
            'success' => true,
        ];

        return $ret;

    }

}
