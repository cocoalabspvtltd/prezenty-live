<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\db\Expression;
use Mail;
use backend\models\Event;
use backend\models\Redeem;
use backend\models\BankAccount;
use backend\models\User;
use backend\models\Products;
use linslin\yii2\curl;

use common\components\CustomHelper;



class ProductSyncController extends Controller
{
    public function actionIndex()
    {
                $id=330;
                
                

     /*   try {
*/
            if(isset($id))
            {
                $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'].'rest/v3/catalog/categories/'.(int)$id.'/products';
                //$getQueryParamData = Yii::$app->request->get();
                $getQueryParamData=array();
               //$requestHeader = Yii::$app->request->headers;
                
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
                print_r('$data');exit;
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
            
            
         /*   
        } catch (ErrorException $e) {
            return $e;
        }   */
    }
}    