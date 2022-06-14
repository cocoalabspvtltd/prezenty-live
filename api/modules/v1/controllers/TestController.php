<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
// use backend\models\Log;
use api\modules\v1\models\Account;
use backend\models\Event;
use backend\models\Music;
use backend\models\Favourite;
use backend\models\EventGiftVoucher;
use backend\models\EventMenuGift;
use backend\models\MenuGift;
use backend\models\MenuGiftItems;
use backend\models\EventParticipant;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;
use common\models\EventFoodVoucher;
use common\models\EventOrder;
use linslin\yii2\curl;

use backend\models\EventSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use backend\models\UpiTransaction;
use common\models\EventOrderInvoice;
use backend\models\BalanceSettlement;
use backend\models\TransferMaster;
use common\components\CustomHelper;



use yii\base\ErrorException;


use yii\helpers\Url;

use backend\models\OrderDetail;

use backend\models\Redeem;

use backend\models\RzpPayments;


use backend\models\Products;

use Mpdf\Mpdf;

use backend\models\TaxSettings;
/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class TestController extends ActiveController
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
    
    public function actionFile($user_id = null, $email = null)
    {
        
        if($user_id!=null){
         $modelUser = User::find()->where(['status'=>1, 'id'=>$user_id])->one();
        }elseif($email != null){
            $modelUser = User::find()->where(['status'=>1, 'email'=>$email])->one();
        }
        $fileName = $modelUser->image_url;
        $path = Yii::$app->params['base_path_profile_images'].$fileName;
        header("Content-Type: application/image");
        header("Content-Transfer-Encoding: Binary");
        // header("Content-Length:".filesize($path_to_zip));
        header("Content-Disposition: attachment; filename=$fileName");
        readfile($path);
        exit;
    }
    
        public function actionMail(){
            
            
            
                    $subject = "Your virtual event has updated well!";
                    $appFromEmail="support@prezenty.in";
                    $email = "maneshvmohanan@gmail.com";
                    
                    $mailData[0]['name'] = "test";
                    $mailData[0]['status'] = "CREATE";
                
                    
        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/create-update-event' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject($subject)->setTextBody('Prezenty')->send();  
        
               return "done";
            
            
            $postData = Yii::$app->request->post();
        
         // send mail
            try{
               
                // $query = "select json_data from product_details where product_id = ".$postData['product_id']." limit 1";
                // $command = Yii::$app->db->createCommand($query);
                // $result = $command->queryAll();
                $InvMasterId = "s";
                $UserD=User::find()->where(['id'=> $postData['user_id']])->one();
                $phoneSmsSend=$UserD->phone_number;
                $nameSmsSend=$UserD->name;
                
                $mailData[0]['voucherImg'] = CustomHelper::getWoohooVoucherImageUrl($postData['product_id']);
                // $mailData[0]['logo'] = Url::base(true).'/ic_logo.png';
                $mailData[0]['giftedBy']=$UserD->name;
                $mailData[0]['invNo']=$InvMasterId;
                $mailData[0]['id']=$InvMasterId;
                $mailData[0]['mob']=$UserD->phone_number;
                $mailData[0]['email']=$UserD->email;
                

                if($postData['order_type']=='FOOD'){
                    $mailData[0]['id']='FOOD_'.$postData['upi_transaction_id'];
                } else if($postData['order_type'] == 'BUY'){
                    $mailData[0]['id']='BUY_'.$postData['rzp_payment_id'];
                } else {
                    $mailData[0]['id']='REDEEM_'.$postData['redeem_transaction_id'];   
                }
                
                // foreach ($data['cards'] as $key => $value) {

                    $mailData[0]['card_number'] = "value['cardNumber']";
                    $mailData[0]['card_pin'] = "value['cardPin']";
                    $mailData[0]['activationCode'] = "value['activationCode']";
                    $mailData[0]['activationUrl'] = "";
                    $validity = '';
                    // if($value['validity'] != '' || $value['validity'] != null){
                    //      $d = new \DateTime($value['validity']);
                    //      $d->format('u');
                    //      $validity = $d->format('d-F-Y');
                    // }
                   
                    $mailData[0]['card_validity'] = $validity;
                    $mailData[0]['name'] = "value['recipientDetails']['name']";
                    $mailData[0]['gift_card'] = "value['productName']";
                    $mailData[0]['amount'] = "value['amount']";
                    $mailData[0]['price'] = "value['amount']";

                    // $mailData[$key]['currency_symbol'] = $data['currency']['symbol'];
                    $mailData[0]['productName'] = "value['sku']";  
                    //[$key]['image'] = $data['products'][$sku]['images']['mobile'];
                    $mailData[0]['content'] = 'wasfgjhh werdtgfhQ  2QWERTG';
                // }                
    
    
                $testArray['mailData']=$mailData;
                $appFromEmail = 'support@prezenty.in';
                $toEmail = "manesh.mvm225@gmail.com"; //$data['cards'][0]['recipientDetails']['email'];
              
                $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/card-details' ] ,$testArray)->setFrom([$appFromEmail => 'Prezenty'])->setTo($toEmail)->setSubject('You got a gift from '.$mailData[0]['giftedBy'])->setTextBody('Order Details')->send();                       
                 
                 return  "done";
            } catch (ErrorException $e) {
                print_r($e);
            }
        }
    
    
    
    
        public function actionSendInvoiceByType(){
    
         $postData = Yii::$app->request->post();
         $mailData=[];
         $appFromEmail="support@prezenty.in";
         $email='';
         if($postData['order_type'] == 'BUY'){
            
            $model = OrderDetail::find()->where(['rzp_payment_id'=>$postData['id'],'order_type'=>'BUY'])->all();
            $modelRazor=RzpPayments::find()->where(['payment_id' => $model[0]->rzp_payment_id])->all();
            
            $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
            $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'--';
                $temp[0]['date']=$modelRazor[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRazor[0]['amount'];
                $temp[0]['id']='BUY_'.$modelRazor[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'--';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                
              
                $amount= count($model)*$model[0]['amount'];
                $gst = $modelTax->option_value;
                
                $rzpCharge = $amount * .02;
                $rzpChargeTax = $rzpCharge * ($gst/100);
                     
                $temp[0]['serviceCharge']=round($rzpCharge,2);
                $temp[0]['taxAmount']=round($rzpChargeTax,2);
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                //print_r($mailData);exit;

         } else if($postData['order_type'] == 'FOOD') {
            
            $model = OrderDetail::find()->where(['upi_transaction_id'=>$postData['id'],'order_type'=>'FOOD'])->all();    

               $temp=array();
                
                foreach ($model as $key => $value) {
                    $modelUpi=UpiTransaction::find()->where(['id' => $value->upi_transaction_id])->all();
                    
                    $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                    $modelProduct=Products::find()->where(['id' => $value->product_id])->all();
                    $modelUser=User::find()->where(['id' => $modelUpi[0]['user_id']])->all();
                
                    $appFromEmail="support@prezenty.in";
                    
                    $temp[$key]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                    $temp[$key]['date']=$model[0]->created_at;
                    $temp[$key]['amountR']=$modelUpi[0]['amount'];
                    $temp[$key]['amount']=$model[0]->amount;
                    $temp[$key]['id']='FOOD_'.$modelUpi[0]['id'];
                    $temp[$key]['status']=$value->status;
                    $temp[$key]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                    $temp[$key]['name']=$modelUser[0]['name'];
                    $temp[$key]['email']=$modelUser[0]['email'];
                    $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                    $temp[$key]['gst']=$modelTax->option_value;
                    $temp[$key]['count']=count($model);
                    $temp[$key]['serviceCharge']=0;
                    $temp[$key]['taxAmount']=0;
                
                    $temp[$key]['voucher']=$modelProduct[0]['name'];
            }
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                
                // return $mailData;
                
            } else{
                
                $model = OrderDetail::find()->where(['redeem_transaction_id'=>$postData['id'],'order_type'=>'REDEEM'])->all(); 
                
                $modelRedeem=Redeem::find()->where(['id' => $model[0]['redeem_transaction_id']])->all();
                $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                $temp[0]['date']=$model[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRedeem[0]['amount'];
                $temp[0]['id']='REDEEM_'.$modelRedeem[0]['id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                $temp[0]['serviceCharge']=0;
                $temp[0]['taxAmount']=0;
                
                $temp[0]['voucher']=$modelProduct[0]['name'];
                $email=$modelUser[0]['email'];
                
                $mailData['mailData']=$temp;
                
            } 

        // $email='maneshvmohanan@gmail.com';

        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Order Summary')->setTextBody('Invoice Details')->send();            
            //$mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/tax-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();                            
        
        $this->renderPartial('@common/mail/tax-invoice1',$mailData);
        $mpdf=new mPDF();
        $mpdf->WriteHTML($this->renderPartial('@common/mail/tax-invoice1',$mailData));
        
        $location= Yii::$app->basePath.'/web/pdf/';
        $path = $mpdf->Output($location.'TaxInvoice.pdf', \Mpdf\Output\Destination::FILE);
        $mail = Yii::$app->mailer->compose()->attach($location.'TaxInvoice.pdf')->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();
        
            $ret = [                    
                'statusCode' => 200,
                'message' => 'Success',
                'success' => true
            ];

        return $ret;         
         
    }    


    
    public function actionCreateva($user_id = 380){
        header('Access-Control-Allow-Origin: *');

        try{
            // { "code": 12, "name": "KARNATAKA" }     
            // { "code": "5818", "description": "Digital Goods: Multi-Category" }
            // { "code": "2", "description": "Partnership" }

            
            $get = Yii::$app->request->get();
            $user_details = User::find()->where(['id'=>$user_id])->one();

            // $customerId = $user_details->email."-".$user_details->id;
            // $customerId = preg_replace('/(\.|@|#|\$|%|\^|&|\*|!|;|:|\'|"|~|`|\?|=|\+|\(|\))/i', '-', $customerId);
            // $customerId="462515993434229934";
            $customerId = "380";
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            

            $curl = curl_init();

            // create v2
            $url = $baseUrlDecentro.'v2/banking/account/virtual';
            $body=array(
                "bank_codes" => ["YESB"],
                "name"=> $user_details->name,
                "email"=> $user_details->email,
                "mobile"=> $user_details->phone_number,
                "address"=> $user_details->address,
                "kyc_verified"=> 1,
                "kyc_check_decentro"=> 0,
                "customer_id"=> $customerId,
                "master_account_alias" => $master_account_alias,
                "upi_onboarding"=>1,
                "pan"=>"AAMCP2658N",
                "state_code"=>12,
                "city"=>"Bangalore",
                "pincode"=>560062,
                "virtual_account_balance_settlement"=>"ENABLED",    
                "upi_onboarding_details" => array(
                            "merchant_category_code"=> "5818",
                            "merchant_business_type"=> "2",
                    "transaction_count_limit_per_day"=> 1000,
                    "transaction_amount_limit_per_day"=> 100000,
                    "transaction_amount_limit_per_transaction"=> 10000
                    )
                );
            
            
           /* $body=array(
                        "bank_codes" => ["YESB"],
                        "name"=> $user_details->name,
                        "email"=> $user_details->email,
                        "mobile"=> $user_details->phone_number,
                        "address"=> $user_details->address,
                        "kyc_verified"=> 1,
                        "kyc_check_decentro"=> 0,
                        "customer_id"=> $customerId, // $buildId[0].'-'.$user_details->id, //'id'.$user_details->id,
                        "master_account_alias" => Yii::$app->params['decentroConfig']['master_account_alias'],
                        "upi_onboarding"=>1,
                        "pan"=>"AAMCP2658N",
                        "state_code"=>12,
                        "city"=>"Bangalore",
                        "pincode"=>560062,
                        
                            // "merchant_category_code"=> "5818",
                            // "merchant_business_type"=> "2",
                            // "transaction_count_limit_per_day"=> 1000,
                            // "transaction_amount_limit_per_day"=> 100000,
                            // "transaction_amount_limit_per_transaction"=> 10000,
                            
                        "virtual_account_balance_settlement"=>"ENABLED",    
                        "upi_onboarding_details" => array(
                            "merchant_category_code"=> "5818",
                            "merchant_business_type"=> "2",
                            "transaction_count_limit_per_day"=> 1000,
                            "transaction_amount_limit_per_day"=> 100000,
                            "transaction_amount_limit_per_transaction"=> 10000
                            )
                        );   */    
            
            
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            print_r($url);
            print_r("\n\nrequestBody\n".json_encode($body));
            print_r("\n\nresponse\n".$response); exit;
            
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    public function actionTest(){
        header('Access-Control-Allow-Origin: *');

        try{
            // return CustomHelper::getWoohooVoucherImageUrl(62);
            
            $get = Yii::$app->request->get();
           
           
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            
            $curl = curl_init();
                
            $url = $baseUrlDecentro.'core_banking/upi/business_type';
            // $url = $baseUrlDecentro.'core_banking/upi/merchant_category';
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            print_r($url);
            // print_r("\n\nrequestBody\n".json_encode($body));
            print_r("\n\nresponse:\n".$response); exit;
            
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    
    public function actionAccount($mobile=null){
        header('Access-Control-Allow-Origin: *');

        try{
            
            $get = Yii::$app->request->get();
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            

            $curl = curl_init();
            
                
            $url = $baseUrlDecentro.'core_banking/account_information/fetch_details?customer_id=380';
            
            // $url = $baseUrlDecentro.'core_banking/account_information/fetch_details?mobile='.$mobile;
            
            // $url = $baseUrlDecentro.'core_banking/account_information/fetch_details?type=virtual&account_number=462515154461343473';
            
            // $url = $baseUrlDecentro.'core_banking/account_information/fetch_details?type=virtual&account_number=462520303019005403';
            
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module",
                    "provider_secret: $provider_secret"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            // print_r($url);
            // print_r("\n\nresponse:\n".$response); exit;
            print_r($response); exit;
            
            
        }catch(Exception $e){
            return $e;
        }
    }
   
   
   
    
    public function actionAccountBalance($is_master_va = 0){
        header('Access-Control-Allow-Origin: *');

        try{
            
            $get = Yii::$app->request->get();
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            
            $curl = curl_init();
            
            if($is_master_va==0){
                // $account_number="462520969136192933";
                // $customer_id="maneshvm11-gmail-com-971";
                $account_number="462515154461343473";
                $customer_id="reshmaps095-gmail-com-974";
            }else{
                $account_number="462520303019005403";
                $customer_id="462515993434229934";
            }
            
            $url = $baseUrlDecentro."core_banking/money_transfer/get_balance?account_number=".$account_number."&customer_id=".$customer_id;
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module",
                    "provider_secret: $provider_secret"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            // print_r($url);
            // print_r("\n\nresponse:\n".$response); exit;
            
            return json_decode($response);
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    
    public function actionSettleVa(){
        header('Access-Control-Allow-Origin: *');

        try{
            
            $get = Yii::$app->request->get();
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            

            $curl = curl_init();
            
            $url = $baseUrlDecentro.'v2/banking/account/virtual/balance_settlement';
            $body=array(
                "baas_account_number" => "462520303019005403",
                "baas_account_ifsc"=> "YESB0CMSNOC"
                );
          
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module",
                    "provider_secret: $provider_secret"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            // print_r($url);
            // print_r("\n\nresponse:\n".$response); exit;
            
            return json_decode($response);
            
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    
    public function actionStatus($transaction_id = null){
        header('Access-Control-Allow-Origin: *');

        try{
            
            $get = Yii::$app->request->get();
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            
            $curl = curl_init();
            
            $url = $baseUrlDecentro."core_banking/money_transfer/get_status?decentro_txn_id=".$transaction_id;
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module",
                    "provider_secret: $provider_secret"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            // print_r($url);
            // print_r("\n\nresponse:\n".$response); exit;
            
            return json_decode($response);
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    
    
    
    
    public function actionStatement(){
        header('Access-Control-Allow-Origin: *');

        try{
            
            $get = Yii::$app->request->get();
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            
            $curl = curl_init();
            
            $url = $baseUrlDecentro."core_banking/money_transfer/get_statement?from=2022-01-01&to=2022-04-01&account_number=462515154461343473&customer_id=reshmaps095-gmail-com-938";
            
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module",
                    "provider_secret: $provider_secret"
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            // print_r($url);
            // print_r("\n\nresponse:\n".$response); exit;
            
            return json_decode($response);
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    
    
    
   public function actionMoneyTransferInit(){

            header('Access-Control-Allow-Origin: *');
            $ret=array();
            $amount="1";
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];            
            $curl = curl_init();
            $bank=array();
            $datetime=date("Y-m-d H-i-s");
            $reference_id=rand().'-Redeem-'.date("His");
            $body=array(
                    "reference_id" => $reference_id,
                    "purpose_message"=> 'Transaction',
                    "from_customer_id"=>"462515993434229934",
                    "from_account"=> '462520303019005403',    
                    "transfer_type"=> 'IMPS', 
                    "mobile_number"=>"7025801819",
                    
                    "to_account"=>"0012053000072937", 
                    
                    "transfer_amount"=> $amount,
                    "currency_code" => "INR",        
                    "beneficiary_details"=> array(
                        "payee_name"=>"manesh",
                        "email_address" => "maneshvmohanan@gmail.com", //'prezentyapp@gmail.com',
                        "mobile_number"=> "9745600196", //'9544855856',
                        "address"=> 'test',
                        "ifsc_code"=> "SIBL0000012", //'YESB0CMSNOC',
                        "country_code"=> "IN",
                        ),
                        
                    // "transfer_datetime"=> $datetime,
                    // "frequency"=> "weekly",
                    // "end_datetime"=> date("Y-m-d H-i-s", strtotime('+1 hours')),
                    );


            curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/initiate',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>json_encode($body),
                      CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "Accept: */*",
                        "client_id: $client_id",
                        "client_secret: $client_secret",
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));                        
            $response = curl_exec($curl);
            // $response=json_decode($response); 
            curl_close($curl);
            
            
            print_r(json_encode($body));
            print_r("\n\n".$response);exit;
            
            
            
        if(isset($response->error)){

            $model=new TransferMaster;
            $model->decentroTxnId=$response->decentroTxnId;
            $model->status=$response->error->status;
            $model->reference_id=$reference_id;
            $model->purpose_message='Transfer';
            $model->from_customer_id='462515993434229934';
            $model->to_account='462515056346649420';
            $model->mobile_number='';
            $model->email_address='';
            $model->name='';
            $model->transfer_type='NEFT';
            $model->transfer_amount=$amount;
            $model->transfer_datetime=$datetime;
            $model->save(false);

            $ret = [
                        'message' => $response->error->message,
                        'statusCode' => 200,
                        'status' => $response->error->status,
                        'success' => false,
                        'id'      => $model->id,                  
                    ]; 

        } else {

            $model=new TransferMaster;
            $model->decentroTxnId=$response->decentroTxnId;
            $model->status=$response->status;
            $model->reference_id=$reference_id;
            $model->purpose_message='Transfer';
            $model->from_customer_id='462515993434229934';
            $model->to_account='462515056346649420';
            $model->mobile_number='';
            $model->email_address='';
            $model->name='';
            $model->transfer_type='NEFT';
            $model->transfer_amount=$amount;
            $model->transfer_datetime=$datetime;
            $model->save(false);

            $ret = [
                        'message' => $response->message,
                        'statusCode' => 200,
                        'success' => true,
                        'status' =>  $response->status,
                        'id' => $model->id,                     
                    ];

        }

        return $this->redirect('money-transfer'); 

    }
 
 
 
    public function actionManage($user_id = 953){
        header('Access-Control-Allow-Origin: *');

        try{
            // { "code": 12, "name": "KARNATAKA" }     
            // { "code": "5818", "description": "Digital Goods: Multi-Category" }
            // { "code": "2", "description": "Partnership" }

            
            $get = Yii::$app->request->get();
            $user_details = User::find()->where(['id'=>$user_id])->one();

            // $customerId = $user_details->email."-".$user_details->id;
            // $customerId = preg_replace('/(\.|@|#|\$|%|\^|&|\*|!|;|:|\'|"|~|`|\?|=|\+|\(|\))/i', '-', $customerId);
            $customerId="462515993434229934";
            
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $account_module=Yii::$app->params['decentroConfig']['account_module'];
            $master_account_alias=Yii::$app->params['decentroConfig']['master_account_alias'];
            

            $curl = curl_init();

            $url = $baseUrlDecentro.'core_banking/account_linking/manage_virtual_account';
            $body=array(
                "action"=>"update",
                "type"=>"virtual",
                "currency_code"=>"INR",
                "name"=> $user_details->name,
                "mobile_number"=> $user_details->phone_number,
                "email_address"=> $user_details->email,
                "address"=> $user_details->address,
                // "bank_codes" => ["YESB"],
                "kyc_verified"=> 1,
                "kyc_check_decentro"=> 0,
                "customer_id"=> $customerId,
                "master_account_alias" => $master_account_alias,
                "upi_onboarding"=>1,
                "pan_number"=>"AAMCP2658N",
                "state_code"=>12,
                "city"=>"Bangalore",
                "pincode"=>560062,
                "virtual_account_balance_settlement"=>"ENABLED",    
                "upi_onboarding_details" => array(
                    "merchant_category_code"=> "5818",
                    "merchant_business_type"=> "2",
                    "transaction_count_limit_per_day"=> 1000,
                    "transaction_amount_limit_per_day"=> 100000,
                    "transaction_amount_limit_per_transaction"=> 10000
                    )
                );
            
            
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS =>json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: */*",
                    "client_id: $client_id",
                    "client_secret: $client_secret",
                    "module_secret: $account_module",
                    "provider_secret"=> $provider_secret
                ),
            ));  
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            print_r($url);
            print_r("\n\nrequestBody\n".json_encode($body));
            print_r("\n\nresponse\n".$response); exit;
            
            
        }catch(Exception $e){
            return $e;
        }
    }
    
    
    
}
