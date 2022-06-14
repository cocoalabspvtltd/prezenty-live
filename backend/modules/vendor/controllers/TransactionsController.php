<?php

namespace backend\modules\vendor\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherTransaction;
use backend\models\GiftVoucherTransactionSearch;
use common\components\Sms;
use backend\models\Notification;

class TransactionsController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new GiftVoucherTransactionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
    }

    public function actionClear($id, $status) {
      $model = GiftVoucherTransaction::findOne($id);
      $model->cleared = (int) !$status;
      $model->save();

      $message = "Hi, {$model->vendor->title} gift voucher worth {$model->amount} is active now. "
        . "Scan the bar code shown in your preZenty app to redeem the amount. Cocoalabs";
      Sms::send($model->event->eventUser->phone_number, $message, "1307163773517698813");
          
      $params['message'] = "Hi, A {$model->vendor->title}  gift voucher worth {$model->amount} is active now. 
      Scan the bar code shown in your preZenty app to redeem the amount. ";

      $notification = new Notification();
      $notification->event_id = $model->event_id;
      $notification->participant_id = $model->event->user_id;
      $notification->type = "voucher_active";
      $notification->type_id = null;
      $notification->message = "{$model->vendor->title} gift voucher worth {$model->amount} is active now. "
        . "Scan the bar code shown in your preZenty app to redeem the amount";
      $notification->save(false);

      Yii::$app->mailer->send($model->event->organizer->email, "Prezenty | Redeem your active Gift voucher!", $params);

      $date = date('d-m-Y h:i a');
      $payload = [
        'id' => $model->id,
        'sender_email' => "",
        'date' => $date,
        'time' => $date,
        'event_id' => $model->event_id,
        'event_title' => $model->event->title,
      ];

      Yii::$app->notification->sendToOneUser(
        $message,
        $model->event->organizer->email,
        'message',
        $message,
        false,
        $payload
      );

      return $this->redirect('index');
    }

}
