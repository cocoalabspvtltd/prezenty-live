<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use api\components\Auth;
use backend\models\Product;
use yii\data\ActiveDataProvider;
use backend\models\ProductOrder;
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
        $model = Product::find()->select(['pk_int_id', 'vchr_product_name','p_sku_id','p_price_min','p_price_max','p_image','p_terms_and_conditions'])->where(['pk_int_id' => $id])->One();
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

    public function actionTermsAndConditions($id)
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
    
    public function actionCreateOrder()
    {   
        
        //$post = Yii::$app->request->post();        
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
    try {
        
        $api_key = KEY_ID;
        $amt = $amount * 100;
        $api_secret = KEY_SECRET;
        $api = new Api($api_key, $api_secret);
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
        $ProductOrder->order_amount=$amt;
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


}