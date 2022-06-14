<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use common\models\EventOrder;
use common\models\EventOrderInvoice;
use common\components\Sms;

class OrderPaymentWebhookController extends ActiveController
{
    public $modelClass = 'common\models\EventOrder';

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

    public function actionUpdatePaymentStatus()
    {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Headers: *');
      $payload = Yii::$app->request->post();
      $amount = $payload['payload']['payment']['entity']['amount'] / 100; // Razorpay returns amount in paisa
      $notes = $payload['payload']['payment']['entity']['notes'];
      
      if($payload['event'] == "payment.authorized" && isset($notes['order_id'])) {
        $order_id = $notes['order_id'];
        
        $model = EventOrder::findOne($order_id);
        $eventInvoice = EventOrderInvoice::findOne(['order_id' => $model->id]);
        if(!$eventInvoice) {
          if($amount == $model->total_amount) {
            $model->payment_status = 1;
            $model->save(false);

            $invoice = new EventOrderInvoice();
            $invoice->order_id = $model->id;
            $invoice->amount = $model->amount;
            $invoice->service = $model->service;
            $invoice->gst = $model->gst;
            $invoice->cess = $model->cess;
            $invoice->total_amount = $model->total_amount;
            $invoice->save();

            $message = "Payment of Rs.{$amount} is successful for sending food voucher on " 
              . date('d-m-Y h:i a'). " Order ID {$invoice->id}. Download invoice at http://prezenty.in/inv/" 
              . $invoice->id . ". Cocoalab";

            Sms::send($model->event->eventUser->phone_number, $message, "1307163773504567182");
            
            $params['message'] = "Payment of Rs.{$amount} is successful for sending food voucher on " . date('d-m-Y h:i a') 
            . " Order ID {$invoice->id}. Download invoice at http://prezenty.in/inv/" . $invoice->id;

            Yii::$app->mailer->send($model->event->organizer->email, "Prezenty | Invoice of food voucher", $params);

            return "success {$message}";
          } else {
            return "Amount mismatch";
          }
        } else {
          return "Invoice already generated";
        }
      } else {
        return "Order ID not available";
      }
    }
}
