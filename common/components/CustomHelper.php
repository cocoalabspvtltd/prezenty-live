<?php
namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use DateTime;
use linslin\yii2\curl;

use backend\models\OrderDetail;

use backend\models\Redeem;

use backend\models\RzpPayments;

use backend\models\User;

use backend\models\Products;

use backend\models\UpiTransaction;

use Mpdf\Mpdf;

use backend\models\TaxSettings;

use backend\models\OrderMaster;

use backend\models\InvoiceNo;


class CustomHelper
{
    public static function getSignature($apiURL = null,$getQueryParamData = null,$requestType = null, $postData = null) {
      if($requestType  == 'POST' && $postData != null)
      {
        ksort($postData);
        foreach($postData as $key => $value)
        {
          if(is_array($postData[$key]))
          {
              foreach($postData[$key] as $sub_key => $sub_value)
              {
                if(is_array($postData[$key][$sub_key]))
                {
                  ksort($postData[$key][$sub_key]);
                }
              }
              ksort($postData[$key]);
          }
        }

        $postData= str_replace('+','%20',urlencode(json_encode($postData)));
       
        $apiURL=$requestType.'&'.urlencode($apiURL).'&'.$postData;
        
      }
      else if($getQueryParamData != null && count($getQueryParamData))
      {
        if(count($getQueryParamData)) {
            ksort($getQueryParamData);
            $queryString='?';
            $flag = 0;
            foreach($getQueryParamData as $key => $value)
            {
                if($flag == 0) { $queryString=$queryString.$key.'='.$value; }
                else { $queryString=$queryString.'&'.$key.'='.$value; }
                $flag++;
            }
        }
        $apiURL = $apiURL.$queryString;
        $apiURL=$requestType.'&'.urlencode($apiURL);
      }
      else
      {
        $apiURL=$requestType.'&'.urlencode($apiURL);
      }

      $signature = hash_hmac('sha512', $apiURL, Yii::$app->params['woohooConfig']['clientSecret']); 
      return $signature;
    }

    public static function getDateAtClient() {
      $dateTime = new DateTime();
      $dateAtClient = $dateTime->format('c');
      return $dateAtClient;
    }
    
    public static function getToken(){

            $baseUrl = Yii::$app->params['woohooConfig']['baseAPIURL'];

            $post = Yii::$app->request->post();
            $curl = new curl\Curl();
    
            $postData = array(
                'clientId' => Yii::$app->params['woohooConfig']['clientID'],
                'username' => Yii::$app->params['woohooConfig']['clientUsername'],
                'password' => Yii::$app->params['woohooConfig']['clientPassword']
            );

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL,$baseUrl."oauth2/verify");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($postData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $authCode = curl_exec($curl);            
            $authCode=json_decode($authCode);
            curl_close($curl);
              
            
            $postData = array(
                'clientId' => Yii::$app->params['woohooConfig']['clientID'],
                'authorizationCode' => $authCode->authorizationCode,
                'clientSecret' => Yii::$app->params['woohooConfig']['clientSecret']
            );
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL,$baseUrl."oauth2/token");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($postData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $token = curl_exec($curl);
            $token = json_decode($token);
            curl_close($curl);
            
            return $token;

    }    
    
    
    public static function getWoohooVoucherImageUrl($id){

        $prod = Products::find()->where(['id'=>$id])->one();
        
        $image = '';
        if($prod->image != null){
            $image = Yii::$app->params['base_path_woohoo_images'].$prod->image;
        }else{
            $image = $prod->image_mobile;
        }
        return $image;
    } 
    
    public static function sendSmsCard($phoneSmsRec,$v1,$v2,$v3){
        
        $curl = curl_init();
        $country_code=91;
        $v3 = preg_replace('/[^A-Za-z0-9\-]/', '%20', $v3);
        //$v3='APITESTING%20CN%20PIN';
        $phoneSmsRec = preg_replace("/^\+?{$country_code}/", '',$phoneSmsRec);
/*        print_r($v1);
        print_r($v2);
        print_r($v3);
        print_r($phoneSmsRec);exit;*/
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsRec.'&sender=PRZNTY&message=WoW!%20Prezenty%20successfully%20sent%20your%20EGift%20card%20via%20email%20and%20SMS.%20Gift%20card%20No:'.$v1.'%20Activation%20No:'.$v2.'%20Merchant%20Name:'.$v3.'%20Enjoy%20the%20day%20with%20Prezenty.%20To%20explore%20more,%20check%20out%20the%20Prezenty%20web:%20https://prezenty.in/&template_id=1307164760863532508',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        curl_close($curl); 
        
        return true;
    }   
    
    public static function sendSmsRecipient($phoneSmsSend,$nameSmsRec){    
        
/*        print_r($nameSmsRec);
        echo '<pr>';
        print_r($phoneSmsSend);exit;*/
        
        $nameSmsRec=str_replace(' ', '%20', $nameSmsRec);
        
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Your%20instant%20gift%20card%20was%20successfully%20processed%20and%20dispatched%20to%20'.$nameSmsRec.'%20via%20email%20and%20SMS%20Thank%20you%20for%20shopping%20with%20us%20Find%20more%20gifts%20to%20shop%20with%20the%20Prezenty%20web:%20https://prezenty.in/&template_id=1307164760849457221',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    
                     return true;
    }                    
    

     
    public static function sendSmsSender($phoneSmsSend,$nameSmsSend,$InvMasterId,$refSmsNo,$dateSms){
        
                    $curl = curl_init();
              /*      
                    print_r($phoneSmsSend);
                    echo '<pre>';
                    print_r($refSmsNo);
                    echo '<pre>';
                    print_r($refSmsNo);
                    echo '<pre>';
                    print_r($refSmsNo);*/
                    
                    
        $nameSmsSend=str_replace(' ', '%20', $nameSmsSend);
        
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=inR43eNJeRdeyFBBqsCMlT4eijCX3Vt1&type=1&to='.$phoneSmsSend.'&sender=PRZNTY&message=Hello%20'.$nameSmsSend.'We%20received%20your%20order%20'.$InvMasterId.'%20with%20Rf.No:%20'.$refSmsNo.'%20on%20Date%20'.$dateSms.'%20We%20will%20keep%20you%20informed%20once%20dispatched.%20https://prezenty.in/&template_id=1307164785061419084',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        ));
                
                        $response = curl_exec($curl);
                        curl_close($curl); 
                        
                         return true;
    }
    public static function sendInvoiceByType($id,$order_type){
    
         //$postData = Yii::$app->request->post();
         $mailData=[];
         $appFromEmail="support@prezenty.in";
         $email='';
         if($order_type == 'BUY'){
            
            $model = OrderMaster::find()->where(['rzp_payment_id'=>$id,'order_type'=>'BUY'])->all();
            
            $modelOrderDetail = OrderDetail::find()->where(['rzp_payment_id'=>$id,'order_type'=>'BUY'])->all();
            
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
                $temp[0]['type']='BUY';
                $temp[0]['id']='BUY_'.$modelRazor[0]['payment_id'];
                $temp[0]['status']=$model[0]['status'];
                $temp[0]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'--';
                $temp[0]['name']=$modelUser[0]['name'];
                $temp[0]['email']=$modelUser[0]['email'];
                $temp[0]['gst']=$modelTax->option_value;
                $temp[0]['count']=count($model);
                $temp[0]['invNo']=$model[0]->inv_no_id;
                $temp[0]['order_id']=$model[0]->order_id;
                // $temp[0]['image']=$modelProduct[0]['image_small'];
                $temp[0]['image'] = CustomHelper::getWoohooVoucherImageUrl($model[0]['product_id']);
                $temp[0]['rece_name']=$modelOrderDetail[0]->recipient_name;
                $temp[0]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                $temp[0]['rece_email']=$modelOrderDetail[0]->recipient_email;
                
            
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

         } else if($order_type == 'FOOD') {
            
            $model = OrderMaster::find()->where(['upi_transaction_id'=>$id,'order_type'=>'FOOD'])->all();
            $modelOrderDetail = OrderDetail::find()->where(['upi_transaction_id'=>$id,'order_type'=>'FOOD'])->all();

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
                    $temp[$key]['type']='FOOD';
                    $temp[$key]['status']=$value->status;
                    $temp[$key]['address']=isset($modelUser[0]['address'])?$modelUser[0]['address']:'-';
                    $temp[$key]['name']=$modelUser[0]['name'];
                    $temp[$key]['email']=$modelUser[0]['email'];
                    $modelTax = TaxSettings::find()->where(['option_name'=>'gst'])->one();  
                    $temp[$key]['gst']=$modelTax->option_value;
                    $temp[$key]['count']=count($model);
                    $temp[$key]['serviceCharge']=0;
                    $temp[$key]['taxAmount']=0;
                    $temp[$key]['invNo']=$model[0]->inv_no_id;
                    $temp[$key]['order_id']=$model[0]->order_id;
                    $temp[$key]['image']=$modelProduct[0]['image_small'];
                    
                    $temp[$key]['rece_name']=$modelOrderDetail[0]->recipient_name;
                    $temp[$key]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                    $temp[$key]['rece_email']=$modelOrderDetail[0]->recipient_email; 
                    
                    $temp[$key]['voucher']=$modelProduct[0]['name'];
            }
                $mailData['mailData']=$temp;
                $email=$modelUser[0]['email'];
                
                // return $mailData;
                
            } else{
                
                $model = OrderMaster::find()->where(['redeem_transaction_id'=>$id,'order_type'=>'REDEEM'])->all();
                
                 $modelOrderDetail = OrderDetail::find()->where(['redeem_transaction_id'=>$id,'order_type'=>'REDEEM'])->all();
                
                $modelRedeem=Redeem::find()->where(['id' => $model[0]['redeem_transaction_id']])->all();
                $modelUser=User::find()->where(['id' => $model[0]->user_id])->all();
                $modelProduct=Products::find()->where(['id' => $model[0]['product_id']])->all();
            
                $appFromEmail="support@prezenty.in";
                                       
                $temp=[];
                $temp[0]['phone']=isset($modelUser[0]['phone_number'])?$modelUser[0]['phone_number']:'-';
                $temp[0]['date']=$model[0]->created_at;
                $temp[0]['amount']=$model[0]->amount;
                $temp[0]['amountR']=$modelRedeem[0]['amount'];
                $temp[0]['type']='REDEEM';
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
                $temp[0]['invNo']=$model[0]->inv_no_id;
                $temp[0]['voucher']=$modelProduct[0]['name'];
                 $temp[0]['image']=$modelProduct[0]['image_small'];
                $email=$modelUser[0]['email'];
                $temp[0]['order_id']=$model[0]->order_id;
                $temp[0]['rece_name']=$modelOrderDetail[0]->recipient_name;
                $temp[0]['rece_mob']=$modelOrderDetail[0]->recipient_mobile;
                $temp[0]['rece_email']=$modelOrderDetail[0]->recipient_email;                 
                $mailData['mailData']=$temp;
                
            } 

        // $email='albinsunny1996@gmail.com';

        $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/order-summary' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Order Summary')->setTextBody('Invoice Details')->send();  
        
/*            $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/tax-invoice' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Tax Invoice')->setTextBody('Tax Invoice Details')->send();                       */     
        
        $this->renderPartial('@common/mail/tax-invoice',$mailData);
        $mpdf=new mPDF();
        $mpdf->WriteHTML($this->renderPartial('@common/mail/tax-invoice',$mailData));
        
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
}