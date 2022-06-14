<?php
namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use linslin\yii2\curl;
use yii\base\ErrorException;
use backend\models\Products;
use backend\models\ProductDetail;
use common\components\CustomHelper;
use backend\models\Image;

/**
 * Woohoo Catelog Controller
 */

class WoohooProductsController extends ActiveController
{
    public $modelClass = 'backend\models\Products';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['products']);
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

    public function actionProducts($id = null)
    {
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
                       $ret = [                    
                                'statusCode' => 200,
                                'data' => $data,
                                'message' => 'Success',
                                'success' => TRUE
                            ];

                        return $ret;                       
                }
            }
            else
            {
                $code = 500;
                Yii::$app->response->statusCode = 500;
                $message = "Product Category id can not be empty and Category id should be valid";
                $result = [
                    'result'     => [
                        'category_id' => $id
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
    
    public function actionProductDetailOld($sku = null)
    {
        try {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Headers: *');

            if(isset($sku))
            {
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/products/'.$sku;
                $getQueryParamData = array();
                $requestHeader = Yii::$app->request->headers;

                $signature = CustomHelper::getSignature($baseUrl,$getQueryParamData,'GET');
                $dateAtClient = CustomHelper::getDateAtClient();
                $token=CustomHelper::getToken();
                if($signature && $signature != null)
                {
                    $curl = curl_init();  
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrl,
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
                        "Authorization: Bearer $token->token"
                    ),
                ));

                        $response = curl_exec($curl);
                        curl_close($curl);                
                        $urlReturn=json_decode($response);                    
                        $ret = [                    
                                'statusCode' => 200,
                                'data' => json_decode($response),
                                'success' => true
                            ];

                        return $ret;   
                }
            }
            else
            {
                $code = 500;
                Yii::$app->response->statusCode = 500;
                $message = "Product SKU can not be empty and Product SKU should be valid";
                $result = [
                    'result'     => [
                        'SKU' => $sku
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
    
   /* public function actionProductDetail($sku = null)
    {
        try {

            if(isset($sku))
            {
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/products/'.$sku;
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
                                'success' => true
                            ];

                        return $ret;   
                }
            }
            else
            {
                $code = 500;
                Yii::$app->response->statusCode = 500;
                $message = "Product SKU can not be empty and Product SKU should be valid";
                $result = [
                    'result'     => [
                        'SKU' => $sku
                    ],
                    'statusCode' => $code,
                    'message'    => $message
                ];

                return $result;
            }
            
        } catch (ErrorException $e) {
            return $e;
        }
    }*/
    
    public function actionGetProducts($type = null,$cat_id = null)
    {
            header('Access-Control-Allow-Origin: *');
            /*food=1
            gift=2*/
            
/*            if($type!="null"){
                
                $products = Products::find()->where(['voucher_type'=>$type])->all();
                
            } else {

                $products = Products::find()->all();
            }*/

           if($type!="null"){
                if($cat_id !="null"){                            
                    //$products = Products::find()->where(['voucher_type'=>$type])->whereAnd(['category_id'=>$cat_id])->whereAnd(['status'=>1])->all();
                    
                    $products = Products::find()->where(['voucher_type'=>$type,'category_id'=>$cat_id,'status'=>1])->all();
                } else {
                    //$products = Products::find()->where(['voucher_type'=>$type])->whereAnd(['status'=>1])->all();
                    
                    $products = Products::find()->where(['voucher_type'=>$type,'status'=>1])->all();
                } 
            } else {
                if($cat_id !="null"){
                    
                    //$products = Products::find()->where(['category_id'=>$cat_id])->whereAnd(['status'=>1])->all();
                    
                    $products = Products::find()->where(['category_id'=>$cat_id,'status'=>1])->all();
                    
                } else {
                    $products = Products::find()->where(['status'=>1])->all();
                }
            }            
            
            
            $ret = [                    
                                'statusCode' => 200,
                                'base_path_woohoo_images' => Yii::$app->params['base_path_woohoo_images'],
                                'data' => $products,
                                'message' => 'Success',
                                'success' => TRUE
                            ];

            return $ret;         
    }

    public function actionProductDetail($id = null)
    {       
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');

        try {

            if(isset($id))
            {
                $product=ProductDetail::find()->where(['product_id'=>$id])->one();    
                $img=Image::find()->where(['status'=>1])->all();
                if(!empty($product)){

                    $prod = Products::find()->where(['id'=>$product->product_id])->one();
                   
                    $ret = [                    
                            'statusCode' => 200,
                            'image'=>CustomHelper::getWoohooVoucherImageUrl($id),
                            'data' => json_decode($product->json_data),
                            'base_path_templates_images' => Yii::$app->params['base_path_image_template_files'],
                            'templates'=>$img,
                            'offers' => $prod->offers,
                            'success' => true
                        ];
    
                    return $ret;

                } else {
    
                    $product = Products::find()->where(['id'=>$id])->one();
    
                    $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/products/'.$product->sku;
                    $getQueryParamData = array();
                    $requestHeader = Yii::$app->request->headers;
    
                    $signature = CustomHelper::getSignature($baseUrl,$getQueryParamData,'GET');
                    $dateAtClient = CustomHelper::getDateAtClient();
                    $token=CustomHelper::getToken();
                    if($signature && $signature != null)
                    {
                        $curl = curl_init();  
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => $baseUrl,
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
                            "Authorization: Bearer $token->token"
                        ),
                    ));
                        $response = curl_exec($curl);
                        curl_close($curl);                
                        $urlReturn=json_decode($response);
                         $model = new ProductDetail();
                         $model->product_id=$id;
                         $model->json_data=$response;
                         $model->save(false);              
                            $ret = [                    
                                    'statusCode' => 200,
                                    'image'=>CustomHelper::getWoohooVoucherImageUrl($id),
                                    'data' => json_decode($response),
                                    'base_path_templates_images' => Yii::$app->params['base_path_image_template_files'],
                                    'templates'=>$img,                                    
                                    'offers' => null,
                                    'success' => true
                                ];

                            return $ret;   
                    }
                }
            }
            else
            {
                $code = 500;
                Yii::$app->response->statusCode = 500;
                $message = "Product id can not be empty and Product id should be valid";
                $result = [
                    'result'     => [
                        'ID' => $id
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
    
    public function actionGetProductDetails($id)
    { 
            header('Access-Control-Allow-Origin: *');
            
            $product = Products::find()->where(['id'=>$id])->one();
            if($product){
                
                $ret = [                    
                        'statusCode' => 200,
                        'data' => $product,
                        'message' => 'Success',
                        'success' => TRUE
                    ];                

            } else {

                $ret = [                    
                        'statusCode' => 200,
                        'data' => null,
                        'message' => 'Null',
                        'success' => False
                    ]; 
            }


            return $ret;         
    }     
}