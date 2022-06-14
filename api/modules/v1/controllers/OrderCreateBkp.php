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





    public function actionCreate()

    {
        
    	try {

            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: *');

    		$baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders';

            $requestHeader = Yii::$app->request->headers;

            $postData = Yii::$app->request->post();
            
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



            $filterParamData = array();



            $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'POST',$postData);



            $dateAtClient = CustomHelper::getDateAtClient();



            if($signature && $signature != null)

            {

                $curl = new curl\Curl();



                $response = $curl->setRawPostData(json_encode(

                $postData))->setHeaders([

                            'Content-Type' => 'application/json',
                            
                            'Accept' => '*/*',

                            'signature' => $signature,

                            'dateAtClient' => $dateAtClient,

                            'authorization'=> $requestHeader['authorization']

                        ])->post($baseUrl,true);

            }

            $data = (array) json_decode($response,true);
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
                
                $mailData = [];
                $mailData['logo'] = Url::base(true).'/ic_logo.png'; ;
                $mailData['card_number'] = $data['cards'][0]['cardNumber'];
                $mailData['card_pin'] = $data['cards'][0]['cardPin'];
                $validity = '';
                if($data['cards'][0]['validity'] != '' || $data['cards'][0]['validity'] != null){
                     $d = new \DateTime($data['cards'][0]['validity']);
                     $d->format('u');
                     $validity = $d->format('d-F-Y');
                }
               

                $mailData['card_validity'] = $validity;
                $mailData['name'] = $data['cards'][0]['recipientDetails']['name'];
                $mailData['gift_card'] = $data['cards'][0]['productName'];
                $mailData['currency_symbol'] = $data['currency']['symbol'];
                $mailData['price'] = $data['cards'][0]['amount'];

                $sku = $data['cards'][0]['sku'];
                $mailData['image'] = $data['products'][$sku]['images']['base'];
                $mailData['content'] = '';

                //Get Product details by sku
                 $sbaseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/products/'.$sku;
                 $skusignature = CustomHelper::getSignature($sbaseUrl,[],'GET');
                 if($skusignature && $skusignature != null)
                    {
                        $curl = new curl\Curl();
                        $sresponse = $curl->setHeaders([
                                'Content-Type' => 'application/json',
                                'signature' => $skusignature,
                                'dateAtClient' => $dateAtClient,
                                'authorization'=> $requestHeader['authorization']
                            ])->get($sbaseUrl,true);
                        $sdata = (array) json_decode($sresponse,true);
                        if($sdata){
                            $mailData['content'] = $sdata['tnc']['content'];
                        }
                    }
                //End

                // Save order details into db
                $order = new OrderDetail();
                $order->sku = $sku;
                $order->order_id = $data['orderId'];
                $order->reference_number = $data['refno'];
                $order->status = $data['status'];
                $order->card_number = $data['cards'][0]['cardNumber'];
                $order->card_pin = $data['cards'][0]['cardPin'];
                $order->activation_code = $data['cards'][0]['activationCode'];
                $order->barcode = $data['cards'][0]['barcode'];
                $order->activation_url =  $data['cards'][0]['activationUrl'];
                $order->amount = $data['cards'][0]['amount'];
                $order->validity = $data['cards'][0]['validity'];
                $order->issuance_date = $data['cards'][0]['issuanceDate'];
                $order->card_id = $data['cards'][0]['cardId'];
                $order->recipient_name = $data['cards'][0]['recipientDetails']['name'];
                $order->recipient_email = $data['cards'][0]['recipientDetails']['email'];
                $order->recipient_mobile = $data['cards'][0]['recipientDetails']['mobileNumber'];
                $order->recipient_country = $postData['address']['country'];
                $order->save();
                //end

                $appFromEmail = 'prezentyapp@gmail.com';
                $toEmail = $data['cards'][0]['recipientDetails']['email'];
                $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-complete' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo(['albinworkz@gmail.com',$toEmail])->setCc('support@prezentystore.com')->setSubject('Prezenty Order Details')->setTextBody('test')->send();
            }
                   $ret = [                    
                            'statusCode' => 200,
                            'data' => json_decode($response),
                            'message' => 'Success',
                            'success' => true
                        ];

                    return $ret; 
        } catch (ErrorException $e) {

            return $e;

        }

    }



    public function actionBalance()

    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        
        try {

            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/balance';

            $requestHeader = Yii::$app->request->headers;

            $postData = Yii::$app->request->post();



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

                            'authorization'=> $requestHeader['authorization']

                        ])->post($baseUrl,true);

            }

           $ret = [                    
                    'statusCode' => 200,
                    'data' => json_decode($response),
                    'message' => 'Success',
                    'success' => true
                ];

            return $ret;             

        } catch (ErrorException $e) {

            return $e;

        }

    }

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

}