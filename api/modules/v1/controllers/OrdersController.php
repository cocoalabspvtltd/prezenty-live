<?php

namespace api\modules\v1\controllers;
use yii;
use yii\data\Pagination;
use yii\rest\ActiveController;
use api\components\Auth;

use backend\models\EventParticipant;
use common\models\EventOrder;
use common\models\EventFoodVoucher;
use common\models\EventOrderTaxSetting;
use backend\models\TaxSettings;

class OrdersController extends ActiveController
{
    public $modelClass = 'common\models\EventOrder';

    public function beforeAction($action)
    {
       $auth = Auth::validateToken();
       if(count($auth) > 0) {
         $this->asJson($auth);
         return false;
       }

       return parent::beforeAction($action);
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['index']);
        unset($actions['view']);
        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            // For cross-domain AJAX request
            'corsFilter'  => [
                'class' => \yii\filters\Cors::className(),
                'cors'  => [
                    // restrict access to domains:
                    'Origin'                           => ["prezenty.in,www.prezenty.in,localhost"],
                    'Access-Control-Request-Method'    => ["GET", "POST"],
                    'Access-Control-Allow-Credentials' => true,
					'Access-Control-Allow-Headers' => ['content-type'],
                    // 'Access-Control-Max-Age'           => 3600,                 // Cache (seconds)
                    'Access-Control-Request-Headers'           => ["*"],                 // Cache (seconds)
                ],
            ],

        ]);
    }

    public function actionIndex($event_id)
    {
      $data = Yii::$app->request->get();
      $page = isset($data['page'])? $data['page'] : 1;
      $pageSize = isset($data['per_page'])? $data['per_page'] : 20;
      
      $query = EventOrder::find()
        ->with(['event', 'foodVoucher.voucher.brand'])
        ->where(['event_id' => $event_id])
		    ->andWhere(['payment_status'=>1]);
      
      $pagination = new Pagination([
        'totalCount' => $query->count(),
        'pageSize' => $pageSize,
        'page' => $page - 1
      ]);
      
      $hasNextPage = false;
      if(($page * $pageSize) < ($pagination->totalCount)){
        $hasNextPage = true;
      }
      
      $eventOrders = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
            
      $vouchers = EventFoodVoucher::find()
            ->with('voucher.brand')
            ->where(['event_id' => $event_id])
            ->asArray()
            ->all();
      
      $event_brands = [];
      foreach($vouchers as $voucher) {
        $event_brands[$voucher['voucher']['brand']['id']] = $voucher['voucher']['brand'];
      }
      $event_brands = array_values($event_brands);
      
      return [
        'list' => $eventOrders,
        'vouchers' => $event_brands,
        'brand_image_location' => UPLOADS_PATH . '/food-voucher-brands/',
        'page' => (int) $page,
        'perPage' => (int) $pageSize,
        'hasNextPage' => $hasNextPage,
        'totalCount' => (int) $pagination->totalCount,
        'message' => 'Success',
        'success' => true,
        'statusCode' => 200,
      ];
    }

    public function actionCreate($order_id = null)
    {
        $totalAmount = 0;
       
        $participants = EventParticipant::find()
            ->where(['id' => array_keys(Yii::$app->request->post('EventParticipant'))])
            ->indexBy('id')
            ->all();

        EventParticipant::loadMultiple($participants, Yii::$app->request->post());
        
        foreach($participants as $participant) {
          $totalAmount += Yii::$app->request->post('amount') * $participant->order_members_count;
        }        
   
        $data= array();            
        $data[0]['key']='Amount';
        $data[0]['value'] = '₹ '.round($totalAmount,2);
        
        $data[1]['key']='Service charge';
        $data[1]['value'] = '₹ 0';        
        
        $data[2]['key']='Gst';
        $data[2]['value'] = '0%'; 
        
        $data[3]['key']='Payable amount';
        $data[3]['value'] = '₹ '.round($totalAmount,2);        
        

      return [
        'amount' => round($totalAmount,2),
        'data' => $data,
        'order_id' => 0,
        'message' => 'Success',
        'success' => true,
        'statusCode' => 200,
      ];
    }


    public function actionView($id)
    {
      $model = EventOrder::find()
          ->with(['event', 'foodVoucher.voucher.brand', 'participants'])
          ->where(['id' => $id])
          ->asArray()
          ->one();
      
      return [
        'order' => $model,
        'brand_image_location' => UPLOADS_PATH . '/food-voucher-brands/',
        'message' => 'Success',
        'success' => true,
        'statusCode' => 200,
      ];
    }
}