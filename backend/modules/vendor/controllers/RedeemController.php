<?php

namespace backend\modules\vendor\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherRedeem;
use backend\models\GiftVoucher;
use backend\models\User;
use backend\models\GiftVoucherRedeemSearch;
use common\components\Sms;

class RedeemController extends Controller
{
    public function actionIndex() {
        $searchModel = new GiftVoucherRedeemSearch([
          'vendor_id' => Yii::$app->session->get('vendor_id'),
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
    }

    // Update redeemed amount
    public function actionUpdate($id) {
      $model = GiftVoucherRedeem::findOne($id);

      if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $model->save();
        Yii::$app->session->setFlash('success','Updated Successfully');

        return $this->redirect('index');
      }

      return $this->render('update', [
        'model' => $model
      ]);
    }

    public function actionCreate()
    {
        $model = new GiftVoucherRedeem();
        $total = null;
        $redeemed = null;
        $balance = null;
        $user = null;

        if($model->load(Yii::$app->request->post())) {
          $data = $model->getBalance($model->mobile);
          
          if(!$data) {
            Yii::$app->session->setFlash('error','User not found');
            return $this->redirect(['index']);
          }

          $total = $data['total'];
          $redeemed = $data['redeemed'];
          $balance = $data['balance'];
          $model->balance = $balance;
          $user = $data['user'];
          $event_gift_voucher = $data['event_gift_voucher'];

          $model->event_gift_voucher_id = $event_gift_voucher->id;
          $model->vendor_id = Yii::$app->session->get('vendor_id');
          $model->user_id = $user->id;
          $model->event_id = $event_gift_voucher->event->id;
          $model->date = date('Y-m-d');

          $model->scenario = "create";
          if($model->validate()) {
            if($model->verify_method == 'otp' && $model->otp != "") {
              if($model->validate()) {
                if($model->save()) {
                  Yii::$app->session->setFlash('success','Redeemed successfully');
                  $this->sendNotification($model, $balance);

                  return $this->redirect(['index']);
                }
              }
            } else if($model->amount != "") {
              if($model->save()) {
                Yii::$app->session->setFlash('success','Redeemed successfully');
                $this->sendNotification($model, $balance);

                return $this->redirect(['index']);
              } else {
                // print_r($model->getErrors());
              }
            }
          }
        }
      
        return $this->render('create', [
          'model' => $model,
          'user' => $user,
          'total' => $total,
          'redeemed' => $redeemed,
          'balance' => $balance
        ]);
    }

    private function sendNotification($model, $balance) {
      $currentBalance = $balance - $model->amount;
      $date = date('d-m-Y h:i a');
      $message = "Hi, Rs{$model->amount} redeemed at {$model->eventGiftVoucher->giftVoucher->title} on {$date}. "
      . "Available balance is Rs{$currentBalance}. Thank you for using preZenty. Cocoalabs";
      Sms::send($model->event->organizer->phone_number, $message, "1307163773526045350");
          
      $params['message'] = "Hi, Rs{$model->amount} redeemed at {$model->eventGiftVoucher->giftVoucher->title} on {$date}. "
      . "Available balance is Rs{$currentBalance}. Thank you for using preZenty";

      Yii::$app->mailer->send($model->event->organizer->email, "Prezenty | {$model->amount}Rs Redeemed", $params);

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
    }

    public function actionGenerateOtp($mobile) {
      $limit = 6;
      $otp = random_int(10 ** ($limit - 1), (10 ** $limit) - 1);

      \Yii::$app->session['otp'] = [
        'mobile' => $mobile,
        'otp' => $otp
      ];

      $user = User::find()
        ->leftJoin('event','event.user_id=user.id')
        ->leftJoin('event_gift_voucher','event_gift_voucher.event_id=event.id')
        ->where(['event_gift_voucher.barcode' => $mobile])
        ->one();
      $from = ['email'=>'info@prezenty.in'];
      $to = $user->email;
      // $to = ['email'=>'jinokt34@gmail.com'];
      $subject = 'Prezenty - OTP';
      $params['message'] = $otp.' is the OTP for the verification process to redeem the voucher. Do not share this with anyone.';
      Yii::$app->mailer->send($to, $subject, $params);

      $phone = $user->phone_number;
      if($phone){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=5uE8xeTySAptrw3zXLIUZ3mjMv6wdWsR&type=1&to='.$phone.'&sender=COCOAL&message='.$otp.'%20is%20the%20OTP%20for%20the%20verification%20process%20to%20redeem%20the%20voucher.%20Do%20not%20share%20this%20with%20anyone.%20Cocoalabs&template_id=1307163327971768496',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
      }

      curl_close($curl);

      return $otp; // Remove this line after implementing SMS
    }

}
