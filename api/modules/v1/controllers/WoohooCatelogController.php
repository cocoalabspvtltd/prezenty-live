<?php
namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use linslin\yii2\curl;
use yii\base\ErrorException;
use backend\models\Category;
use common\components\CustomHelper;

/**
 * Woohoo Catelog Controller
 */

class WoohooCatelogController extends ActiveController
{   
    public $modelClass = 'backend\models\Category';

    public function actions()
    {   
        $actions = parent::actions();
        unset($actions['catelogs']);
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

    public function actionCatelogs($id = null)
    {
        try {
            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/categories';
            if($id != null){
                $baseUrl .= '/'.(int)$id;
            }
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

            $filterParamData = array();

            $signature = CustomHelper::getSignature($baseUrl,$filterParamData,'GET');
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
                
                
                //print_r($response); exit;
                
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
                    $usr_data = Category::find()->where('id = '.$data->id)->all();  
                        if(sizeof($usr_data) == 0){
                            $category = new Category();
                            $category->id = $data->id;
                            $category->name = $data->name;
                            $category->description = $data->description;
                            $category->image = $data->images->image;
                            $category->thumbnail = $data->images->thumbnail;
                            $category->subcategories_count = $data->subcategoriesCount;
                            $category->created_at = date("Y-m-d H:i:s", time());
                            $category->save();
                        }
                    }
                        $ret = [                    
                                'statusCode' => 200,
                                'data' => $data,
                                'message' => 'Created',
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
    
    public function actionGetCatlog()
    {
        $category = Category::find()->all();
        
         $ret = [                    
                 'statusCode' => 200,
                 'data' => $category,
                 'message' => 'Created',
                 'success' => true
                ];
                            
        return $ret;
    }    
}