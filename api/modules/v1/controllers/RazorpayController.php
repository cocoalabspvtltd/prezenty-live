<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use api\modules\v1\models\Account;
use yii\data\ActiveDataProvider;
use Razorpay\Api\Api;
use backend\models\PaymentOrder;
use backend\models\Log;
use backend\models\Order;
use backend\models\MenuOrderPayment;
use backend\models\EventGiftVoucherReceived;
use backend\models\Notification;
use backend\models\EventParticipant;
use backend\models\GiftVoucher;
use backend\models\RzpPayments;
use backend\models\User;
use backend\models\Products;
use linslin\yii2\curl;


/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class RazorpayController extends ActiveController
{
    public $modelClass = 'backend\models\Event';      
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }  

    public function actionGetApiKey(){
        header('Access-Control-Allow-Origin: *');
        $ret = [];
        $statusCode = 200;
        $success = false;
        $msg = '';
        $get = Yii::$app->request->get();
        $apiKey = KEY_ID;
        $ret = [
            'apiKey' => $apiKey,
            'statusCode' => 200,
            'message' => 'Listed Successfully',
            'success' => true,
        ];
        return $ret;
    }
    public function actionGetOrderId(){
        header('Access-Control-Allow-Origin: *');
        $ret = [];
        $statusCode = 200;
        $success = false;
        $msg = '';
        $get = Yii::$app->request->get();
        $amount = isset($get['amount'])?$get['amount']:'';
        $event_id = isset($get['event_id'])?$get['event_id']:'';
        $gift_id = isset($get['gift_id'])?$get['gift_id']:'';
        $participant_email = isset($get['participant_email'])?$get['participant_email']:'';
        if(!$amount || !$event_id){
            $msg = "amount and Event Id cannot be blank.";
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        if($gift_id){
            $recipt_id = $gift_id;
        }else{
            $recipt_id = $event_id;
        }
        $amt = $amount * 100;
        $api_key = KEY_ID;
        $api_secret = KEY_SECRET;
        $api = new Api($api_key, $api_secret);
        $order = $api->order->create(
            array(
                'receipt' => $recipt_id,
                'amount' => $amt,
                'currency' => 'INR'
            )
        );
        $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'email'=>$participant_email,'event_id'=>$event_id])->one();
        // if($modelEventParticipant){
        $modelPaymentOrder = new PaymentOrder;
        $modelPaymentOrder->event_id = $event_id;
        if($gift_id){
            $modelPaymentOrder->gift_id = $gift_id;
        }
        if($modelEventParticipant){
            $modelPaymentOrder->participant_id = $modelEventParticipant->id;
        }
        $modelPaymentOrder->order_id = $order->id;
        $modelPaymentOrder->amount = $amount;
        $modelPaymentOrder->converted_amount = $amt;
        $modelPaymentOrder->currency = 'INR';
        $modelPaymentOrder->save(false);
        $participant_id = null;
        if($modelEventParticipant){
            $participant_id = $modelEventParticipant->id;
        }
        $ret = [
            'orderId' => $order->id,
            'convertedAmount' => $amt,
            'eventParticipantId' => $participant_id,
            'statusCode' => 200,
            'message' => 'Listed Successfully',
            'success' => true,
        ];
        return $ret;
        // }else{
        //     $ret = [
        //         'message' => "Invalid Participant Email",
        //         'statusCode' => 200,
        //         'success' => false
        //     ];
        //     return $ret;
        // }
    }
    public function actionGetPayment(){
        $post = Yii::$app->request->post();
        if($post){
            $paymentEvent = $post['event'];
            if($paymentEvent == 'payment.authorized'){
                $payload = $post['payload'];
                if($payload){
                    $payment = $post['payload']['payment'];
                    if($payment){
                        $entity = $post['payload']['payment']['entity'];
                        if($entity){
                            $paymentId = $post['payload']['payment']['entity']['id'];
                            $convertedAmount = (($post['payload']['payment']['entity']['amount'])/100);
                            $notes = $post['payload']['payment']['entity']['notes'];
                            if($notes){
                                $isOrder = ($post['payload']['payment']['entity']['notes']['iso'])?$post['payload']['payment']['entity']['notes']['iso']:'';
                                if($isOrder){
                                    // $modelLog = new Log;
                                    // $modelLog->meta = json_encode('entered');
                                    // $modelLog->save(false);
                                    $event_id = ($post['payload']['payment']['entity']['notes']['eid'])?$post['payload']['payment']['entity']['notes']['eid']:'';
                                    $participant_ids = ($post['payload']['payment']['entity']['notes']['pids'])?$post['payload']['payment']['entity']['notes']['pids']:null;
                                    $menu_gift_id = ($post['payload']['payment']['entity']['notes']['mgi'])?$post['payload']['payment']['entity']['notes']['mgi']:null;    
                                    $gift_count = ($post['payload']['payment']['entity']['notes']['gc'])?$post['payload']['payment']['entity']['notes']['gc']:0;    
                                    $menu_veg_id = ($post['payload']['payment']['entity']['notes']['mvi'])?$post['payload']['payment']['entity']['notes']['mvi']:null;    
                                    $veg_count = ($post['payload']['payment']['entity']['notes']['vc'])?$post['payload']['payment']['entity']['notes']['vc']:0;    
                                    $menu_non_veg_id = ($post['payload']['payment']['entity']['notes']['mnvi'])?$post['payload']['payment']['entity']['notes']['mnvi']:null;    
                                    $non_veg_count = ($post['payload']['payment']['entity']['notes']['nvc'])?$post['payload']['payment']['entity']['notes']['nvc']:0;    

                                    $modelOrder = new Order;
                                    $modelOrder->event_id = $event_id;
                                    $modelOrder->menu_gift_id = $menu_gift_id;
                                    $modelOrder->gift_count = $gift_count;
                                    $modelOrder->menu_veg_id = $menu_veg_id;
                                    $modelOrder->veg_count = $veg_count;
                                    $modelOrder->menu_non_veg_id = $menu_non_veg_id;
                                    $modelOrder->non_veg_count = $non_veg_count;
                                    $modelOrder->amount = $convertedAmount;
                                    $modelOrder->order_status = 'Being Processed';
                                    $modelOrder->save(false);

                                    // $modelLog = new Log;
                                    // $modelLog->meta = json_encode($modelOrder->getErrors());
                                    // $modelLog->save(false);

                                    if($participant_ids){
                                        $userIds = explode(',', $participant_ids);
                                        foreach($userIds as $userId){
                                            $modelMenuOrderPayment = new MenuOrderPayment;
                                            $modelMenuOrderPayment->event_id = $event_id;
                                            $modelMenuOrderPayment->participant_id = $userId;
                                            $modelMenuOrderPayment->is_paid = 1;
                                            $modelMenuOrderPayment->save(false);
                                        }
                                    }
                                }else{
                                    $event_id = ($post['payload']['payment']['entity']['notes']['eid'])?(int) $post['payload']['payment']['entity']['notes']['eid']:null;
                                    $gift_id = ($post['payload']['payment']['entity']['notes']['gid'])?(int) $post['payload']['payment']['entity']['notes']['gid']:null;
                                    $participant_id = ($post['payload']['payment']['entity']['notes']['pid'])?(int) $post['payload']['payment']['entity']['notes']['pid']:null;
                                    $modelEventGiftVoucherReceived = new EventGiftVoucherReceived;
                                    $modelEventGiftVoucherReceived->event_gift_id = $gift_id;
                                    $modelEventGiftVoucherReceived->event_participant_id = $participant_id;
                                    $modelEventGiftVoucherReceived->event_id = $event_id;
                                    $modelEventGiftVoucherReceived->amount = $convertedAmount;
                                    $modelEventGiftVoucherReceived->transaction_id = $paymentId;
                                    $modelEventGiftVoucherReceived->save(false);
                                    // $no = rand(0, 9999999);
                                    // $rand = str_pad($no, 7, "0", STR_PAD_LEFT);
                                    // $barcodeType = $rand.time();
                                    // $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                    // file_put_contents('../../common/uploads/barcodes/'.$barcodeType.'.png', $generator->getBarcode($barcodeType, $generator::TYPE_CODE_128));
                                    // $modelEventGiftVoucherReceived->barcode = $barcodeType;
                                    // $modelEventGiftVoucherReceived->save(false);
                                    
                                    $modelEventParticipant = EventParticipant::find()->where(['status'=>1,'id'=>$participant_id])->one();
                                    $modelEventGift = GiftVoucher::find()->where(['status'=>1,'id'=>$event_gift_id])->one();
                                    $modelNotification = new Notification;
                                    $modelNotification->event_id = $event_id;
                                    $modelNotification->participant_id = $participant_id;
                                    $modelNotification->type = "gift_voucher";
                                    $modelNotification->type_id = $modelEventGift->id;
                                    $modelNotification->message = $modelEventParticipant->name." sent you a ".$modelEventGift->title." gift voucher";
                                    $modelNotification->save(false);

                                    // Yii::$app->email->sendDonationUser($email,$name,$amount);
                                    // Yii::$app->email->sendDonationAdmin($email,$name,$amount);
                                    // Yii::$app->email->sendReceipt($fundraiserId,$email,$name,$amount);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    
    public function actionGetPaymentOrderId(){
        header('Access-Control-Allow-Origin: *');
        $ret = [];
        $statusCode = 200;
        $success = false;
        $msg = '';
        
        $get = Yii::$app->request->get();
        $amount = isset($get['amount'])?$get['amount']:'';
        
       
        $amt = $amount * 100;
        $api_key = KEY_ID;
        $api_secret = KEY_SECRET;
        $api = new Api($api_key, $api_secret);
        $order = $api->order->create(
            array(
                // 'receipt' => $recipt_id,
                'amount' => $amt,
                'currency' => 'INR'
            )
        );
        
        $ret = [
            'orderId' => $order->id,
            'amount' => $amount,
            'convertedAmount' => $amt,
            'statusCode' => 200,
            'message' => 'Order created',
            'success' => true,
        ];
        return $ret;
        
    }
    
    public function actionWebhookPayment(){
        $post = Yii::$app->request->post();
        if($post){
            $paymentEvent = $post['event'];
            if($paymentEvent == 'payment.authorized'){
                $payload = $post['payload'];
                if($payload){
                    $payment = $post['payload']['payment'];
                    if($payment){
                        $entity = $post['payload']['payment']['entity'];
                        if($entity){
                            $paymentId = $post['payload']['payment']['entity']['id'];
                            $convertedAmount = (($post['payload']['payment']['entity']['amount'])/100);
                            $notes = $post['payload']['payment']['entity']['notes'];
                            
                            if($notes){
                                $type = $notes['type'];
                                if($type == 'BUY_VOUCHER'){
                                    $userId = $notes['user_id'];
                                    $productId = $notes['product_id'];
                                    $amount = $notes['amount'];
                                    $state ="";
                                    if(isset($notes['state'])){
                                        $state = $notes['state'];
                                    }
                                    
                                    $model = new RzpPayments;
                                    $model->payment_id = $paymentId;
                                    $model->user_id = $userId;
                                    $model->product_id = $productId;
                                    $model->amount = $amount;
                                    $model->type = $type;
                                    $model->state = $state;
                                    $model->created_at = date("Y-m-d H:i:s", time());
                                    
                                    $model->save(false);
                                    
/*                                    $UserD=User::find()->where(['id'=> $userId])->one();
                                    $phoneSmsSend=$UserD->phone_number;
                                    $curl = curl_init();
                                    curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Thank%20you!%20Your%20payment%20of%20'.$amount.'%20from%20Prezenty%20is%20confirmed.%20Find%20more%20gifts%20to%20shop%20with%20the%20Prezenty%20web:%20https://prezenty.in&template_id=1307164785056981250',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'GET',
                                    ));
                                    
                                    $response = curl_exec($curl);
                                    curl_close($curl);  */                                            
                                        
                                        /*$user_details=User::find()->where(['id' => $userId])->one();
                                        
                                        $appFromEmail="support@prezenty.in";
                                       
                                        $mailData=[];
                                        $mailData['phone']=$user_details->phone_number;
                                        $mailData['date']=date('d-m-Y h:i a');
                                        $mailData['amount']=$amount;
                                        $mailData['id']=$paymentId;

                                        $productDet=Products::find()->where(['id' => $productId])->one();
                                        $mailData['voucher']=$productDet->name;
                                        
                                        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($user_details->email)->setSubject('Prezenty Invoice')->setTextBody('Invoice Details')->send();                                                                                                            */
                                    
                                }
                                
                            }
                        }
                    }
                }
            }
        }
    }
}