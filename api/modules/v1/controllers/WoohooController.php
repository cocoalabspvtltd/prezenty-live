<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use linslin\yii2\curl;
use yii\base\ErrorException;


class WoohooController extends ActiveController
{   
    public $modelClass = ''; 

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['verify']);
        unset($actions['token']);
        return $actions;
    }
/* public function behaviors()
    {
        $behaviors = parent::behaviors();

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
    }*/

    public function  actionVerify(){

        try {
            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'];

            $post = Yii::$app->request->post();
            $curl = new curl\Curl();
    
            $postData = array(
                'clientId' => Yii::$app->params['woohooConfig']['clientID'],
                'username' => Yii::$app->params['woohooConfig']['clientUsername'],
                'password' => Yii::$app->params['woohooConfig']['clientPassword']
            );

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL,$baseUrl."/oauth2/verify");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($postData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $authCode = curl_exec($curl);            
            $authCode=json_decode($authCode);
            curl_close($curl);
               
            $postData = array(
                'clientId' => Yii::$app->params['woohooConfig']['clientID'],
                'authorizationCode' => $authCode->authorizationCode,
                'clientSecret' => Yii::$app->params['woohooConfig']['clientSecret']
            );
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL,$baseUrl."/oauth2/token");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($postData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $token = curl_exec($curl);
            $token = json_decode($token);
            curl_close($curl);
            $ret = [                    
                        'statusCode' => 200,
                        'token' => $token->token,
                        'message' => 'Created',
                        'success' => true
                ];             
           /*$ret = [                    
                    'statusCode' => 200,
                    'authorizationCode' => $authCode->authorizationCode,
                    'message' => 'Created',
                    'success' => true
                ];*/

        } catch (ErrorException $e) {
            $ret = [
                    'statusCode' => 200,
                    'authorizationCode' => '',
                    'message' => 'Error',
                    'success' => false
                ];
        } 
        return $ret;
    }

    public function  actionToken(){

        $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'];
        
        $post = Yii::$app->request->post();
        try {
            $postData = array(
                'clientId' => Yii::$app->params['woohooConfig']['clientID'],
                'authorizationCode' => $post['authorizationCode'],
                'clientSecret' => Yii::$app->params['woohooConfig']['clientSecret']
            );
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,$baseUrl."/oauth2/token");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($postData));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $token = curl_exec($curl);
        $token = json_decode($token);
        curl_close($curl);
        $ret = [                    
                    'statusCode' => 200,
                    'token' => $token->token,
                    'message' => 'Created',
                    'success' => true
            ];        
        } catch (ErrorException $e) {
            
            $ret = [                    
                    'statusCode' => 200,
                    'token' =>'',
                    'message' => 'Failed',
                    'success' => false
            ];
        }

        return $ret;
    }    

    public function actionCreateOrder()
    
    {   
       $post = Yii::$app->request->post();
        $ret = [];
        $statusCode = 200;
        $success = false;
        $msg = '';

        $product_id = isset($post['product_id'])?$post['product_id']:'';
        $amount = isset($post['amount'])?$post['amount']:'';
        $userId = isset($post['userId'])?$post['userId']:'';
        $body = isset($post['body'])?$post['body']:'';

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
            $ProductOrder->woohoo_body=$body;
            $ProductOrder->order_amount=$amount;
            $ProductOrder->order_razor_pay_id=$order->id;
            $ProductOrder->fk_int_product_id=$product_id;
            $ProductOrder->fk_int_user_id=$userId;
            $ProductOrder->order_status='PENDING';
            $ProductOrder->save(false);


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

}
