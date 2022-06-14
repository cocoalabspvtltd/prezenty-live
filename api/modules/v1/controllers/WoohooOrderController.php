<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use linslin\yii2\curl;
use yii\base\ErrorException;
use backend\models\Products;
use common\models\EventOrderTaxSetting;
use common\components\CustomHelper;
use yii\helpers\Url;

/**
 * Woohoo Order Controller
 */

class WoohooOrderController extends ActiveController
{
    public $modelClass = '';
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['orderList']);
        unset($actions['orderDetail']);
        unset($actions['createNew']); 
        unset($actions['orderStatus']);
        unset($actions['activatedCards']);
        unset($actions['orderResend']);
        unset($actions['transactionHistory']);
        return $actions;
    }

    public function behaviors()
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
    }

    public function actionOrderList() 
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        try {
          $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders';
          $getQueryParamData = array();
          $requestHeader = Yii::$app->request->headers;

          $signature = CustomHelper::getSignature($baseUrl,$getQueryParamData,'GET');
          $dateAtClient = CustomHelper::getDateAtClient();

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

          if($signature && $signature != null)
          {
              $curl = new curl\Curl();
              $response = $curl->setHeaders([
                      'Content-Type' => 'application/json',
                      'signature' => $signature,
                      'dateAtClient' => $dateAtClient,
                      'authorization'=> $requestHeader['authorization']
                  ])->get($baseUrl,true);
              
                   $ret = [                    
                            'statusCode' => 200,
                            'data' => json_decode($response),
                            'message' => 'Success',
                            'success' => true
                        ];

                    return $ret;                 
          }
          else
          {
              $code = 500;
              Yii::$app->response->statusCode = 500;
              $message = "Signature is not valid.";
              $result = [
                  'result'     => [
                      'signature' => $signature
                  ],
                  'statusCode' => $code,
                  'message'    => $message
              ];

              return $result;
          }
        } catch (ErrorException $e) {
            return $e;
        }
    }

    public function actionOrderDetail($order_id = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
      try {

        if(isset($order_id))
        {
            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders/'.(int)$order_id;
            $getQueryParamData = array();
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

            $signature = CustomHelper::getSignature($baseUrl,$getQueryParamData,'GET');
            $dateAtClient = CustomHelper::getDateAtClient();

            if($signature && $signature != null)
            {
                $curl = new curl\Curl();
                $response = $curl->setHeaders([
                        'Content-Type' => 'application/json',
                        'signature' => $signature,
                        'dateAtClient' => $dateAtClient,
                        'authorization'=> $requestHeader['authorization']
                    ])->get($baseUrl,true);
                
                   $ret = [                    
                            'statusCode' => 200,
                            'data' => json_decode($response),
                            'message' => 'Success',
                            'success' => true
                        ];

                    return $ret; 
            }
        }
        else
        {
            $code = 500;
            Yii::$app->response->statusCode = 500;
            $message = "Order ID can not be empty and Order ID should be valid";
            $result = [
                'result'     => [
                    'order_id' => $order_id
                ],
                'statusCode' => $code,
                'message'    => $message
            ];

            return $result;
        }
        
      } catch (ErrorException $e) {
          return $e;
      }
    }

    public function actionOrderStatus($ref_id = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        
        try {
            if(isset($ref_id))
            {
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/order/'.$ref_id.'/status';
                $getQueryParamData = array();
                $requestHeader = Yii::$app->request->headers;
      
                $signature = CustomHelper::getSignature($baseUrl,$getQueryParamData,'GET');
                $dateAtClient = CustomHelper::getDateAtClient();
                
                $verify = CustomHelper::getToken();

/*                if(!isset($requestHeader['authorization']))
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
                }*/

                if($signature && $signature != null)
                {
                    $curl = new curl\Curl();
                    $response = $curl->setHeaders([
                            'Content-Type' => 'application/json',
                            'signature' => $signature,
                            'dateAtClient' => $dateAtClient,
                            'authorization'=> 'Bearer '.$verify->token
                        ])->get($baseUrl,true);
                    
                   $ret = [                    
                            'statusCode' => 200,
                            'data' => json_decode($response),
                            'message' => 'Success',
                            'success' => true
                        ];

                    return $ret;                    
                }
                else
                {
                    $code = 500;
                    Yii::$app->response->statusCode = 500;
                    $message = "Signature is not valid.";
                    $result = [
                        'result'     => [
                            'signature' => $signature
                        ],
                        'statusCode' => $code,
                        'message'    => $message
                    ];

                    return $result;
                }
            }
        } catch (ErrorException $e) {
            return $e;
        }
    }

    public function actionActivatedCards($order_id = null)
    {
        try {
            if(isset($order_id))
            {
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

/*                if(!isset($requestHeader['authorization']))
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
                }*/

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
                        
                        $mailData = [];
                        $mailData['logo'] = Url::base(true).'/ic_logo.png';
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
                        $appFromEmail = 'orders@prezentystore.com';
                        //$toEmail = $data['cards'][0]['recipientDetails']['email'];
                        $toEmail = $data['cards'][0]['recipientDetails']['email'];
                        
                        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-complete' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo(['orders@prezentystore.com',$toEmail])->setCc('support@prezentystore.com')->setSubject('Prezenty Order Details')->setTextBody('test')->send();                    
                        
                    $ret = [                    
                            'statusCode' => 200,
                            'data' => json_decode($response),
                            'message' => 'Success',
                            'success' => true
                        ];

                    return $ret;                    
                }
                else
                {
                    $code = 500;
                    Yii::$app->response->statusCode = 500;
                    $message = "Signature is not valid.";
                    $result = [
                        'result'     => [
                            'signature' => $signature
                        ],
                        'statusCode' => $code,
                        'message'    => $message
                    ];

                    return $result;
                }
            }
        } catch (ErrorException $e) {
            return $e;
        }
    }
    public function actionOrderResend($increment_id = null)
    {
         try {
            if(isset($increment_id))
            {
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/orders/'.$increment_id.'/resend';
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
            }
        } catch (ErrorException $e) {
            return $e;
        }
    }
    public function actionTransactionHistory()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        
        try {
            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/transaction/history';
            $requestHeader = Yii::$app->request->headers;
            $postData = Yii::$app->request->post();
            
            $verify = CustomHelper::getToken();

/*            if(!isset($requestHeader['authorization']))
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
            }*/

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
                            'message' => 'Transaction history not found',
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
    
    public function actionGetTaxValue()
    {
        $postData = Yii::$app->request->post();
        $data=EventOrderTaxSetting::find()->where(['option_name'=>'gst'])->all();
       
        $amount=$postData['amount'];
        
        $gst = $data[0]['option_value'];
        $rzpCharge = $amount * .02;
        $rzpChargeTax = $rzpCharge * ($gst/100);
        $payableAmount = $rzpCharge + $rzpChargeTax + $amount;
             
        $data= array();            
        $data[0]['key']='Amount';
        $data[0]['value'] = '₹ '.number_format((float) round($amount,2), 2, '.', '');
        // $data[0]['value'] = '₹ '.round($amount,2);

        $data[1]['key']='Service charge';
        $data[1]['value'] = '₹ '.number_format((float) round($rzpCharge,2), 2, '.', '');
        // $data[1]['value'] = '₹ '.round($rzpCharge,2);        

        $data[2]['key']='Gst';
        $data[2]['value'] = round($gst,2).'% (₹'.number_format((float) round($rzpChargeTax,2), 2, '.', '').')'; 
        // $data[2]['value'] = round($gst,2).'% (₹'.round($rzpChargeTax,2).')'; 

        $data[3]['key']='Payable amount';
        $data[3]['value'] = '₹ '.number_format((float) round($payableAmount,2), 2, '.', '');      
        // $data[3]['value'] = '₹ '.round($payableAmount,2);        
        
        $ret = [
                'statusCode' => 200,
                'amount' => number_format((float) round($payableAmount,2), 2, '.', ''),
                // 'amount' => round($payableAmount,2),
                'data' => $data,
                'message' => 'Success',
                'success' => true
            ];
        
        return $ret;
        
    }


     public function actionGetRedeemTaxValue($order_id = null)
    {
        $postData = Yii::$app->request->post();
       
        $totalAmount=$postData['amount'];
          
   
        $data= array();            
        $data[0]['key']='Amount';
        $data[0]['value'] = '₹ '.number_format((float) round($totalAmount,2), 2, '.', '');
        // $data[0]['value'] = '₹ '.round($totalAmount,2);
        
        $data[1]['key']='Service charge';
        $data[1]['value'] = '₹ 0.00';        
        
        $data[2]['key']='Gst';
        $data[2]['value'] = '0%'; 
        
        $data[3]['key']='Payable amount';
        $data[3]['value'] = '₹ '.number_format((float) round($totalAmount,2), 2, '.', '');  
        // $data[3]['value'] = '₹ '.round($totalAmount,2);        
        

      return [
        'amount' => number_format((float) round($totalAmount,2), 2, '.', ''),
        // 'amount' => round($totalAmount,2),
        'data' => $data,
        'order_id' => 0,
        'message' => 'Success',
        'success' => true,
        'statusCode' => 200,
      ];
    }

}