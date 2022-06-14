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
use Mpdf\Mpdf;
use backend\models\BalanceSettlement;
use backend\models\TransferMaster;

class EventReportController extends Controller
{
    public function actionIndex()
    {
        $appFromEmail="support@prezenty.in";
       // $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Week Summary')->setTextBody('Summary Details')->send();                                                                                                                        
        $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone) );
      
        $events = Event::find()
          ->where(['>=', 'date', $date->format('Y-m-d')])
          ->andWhere(['>=', 'time', $date->format('H:i:s')])
          ->andWhere(['status'=> 1])
          ->all();
        $sum=array();
        $sumRedeem=0;
        $sumUpiFood=0;
        $sumUpiGift=0;

        foreach ($events as $key => $value) {
           
          if(!empty($value['user_id'])){
    
                $sumRedeem=0;
                $sumUpiFood=0;
                $sumUpiGift=0;    
                
            $queryRedeem = (new \yii\db\Query())->from('redeem_master')
                   ->where(['event_id' => $value['id']]);
                
            $sumRedeem = $queryRedeem->sum('amount');
            
            $queryUpiFood = (new \yii\db\Query())->from('upi_transaction_master')
                   ->where(['event_id' => $value['id'],'voucher_type' => 'FOOD','status' => 'SUCCESS']);
            $sumUpiFood = $queryUpiFood->sum('amount');
                      
            $queryUpiGift = (new \yii\db\Query())->from('upi_transaction_master')
                   ->where(['event_id' => $value['id'],'voucher_type' => 'GIFT','status' => 'SUCCESS']);                   
            $sumUpiGift = $queryUpiGift->sum('amount');            
            
            $queryUser = (new \yii\db\Query())->from('user')
                   ->where(['id' => $value['user_id']]);
            $queryUser=$queryUser->all();
            
            $queryEvent = (new \yii\db\Query())->from('event')
                   ->where(['id' => $value['id']]);
            $queryEvent=$queryEvent->all();

            $mailData=[];
            $temp=[];
           
            $temp[0]['name']=$queryUser[0]['name'];
            $temp[0]['e_name']=$queryEvent[0]['title'];
            $temp[0]['e_date']=$queryEvent[0]['date'];
            $temp[0]['sumRedeem']=(isset($sumRedeem)) ? $sumRedeem : 0;
            $temp[0]['sumUpiFood']=(isset($sumUpiFood)) ? $sumUpiFood : 0;
            $temp[0]['sumUpiGift']=(isset($sumUpiGift)) ? $sumUpiGift : 0;
            $mailData['mailData']=$temp;

            $appFromEmail="support@prezenty.in";
                      
            $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/daily-summary' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo($queryUser[0]['email'])->setSubject('Prezenty Day Summary Of Event'.$queryEvent[0]['title'])->setTextBody('Summary Details')->send();                                                                                                                        
              
            
          }
        } 
          
    }
    
    public function actionTransaction()
    {
        $appFromEmail="support@prezenty.in";
       // $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Week Summary')->setTextBody('Summary Details')->send();                                                                                                                        
        $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone) );
      
        $events = Event::find()
          ->where('DATE(created_at)=CURDATE()')
          ->andWhere(['status'=> 1])
          ->count();
        
        
        
        $sum=array();
        $sumRedeem=0;
        $sumUpiFood=0;
        $sumUpiGift=0;

            
                
            $queryRedeem = (new \yii\db\Query())->from('redeem_master')
                   ->where('DATE(created_at)=CURDATE()');
                
            $sumRedeem = $queryRedeem->sum('amount');
            
            $transfer_master=(new \yii\db\Query())->from('transfer_master')
                   ->where('DATE(created_at)=CURDATE()')
                   ->andWhere(['status' => 'success']);
            $transfer_master = $transfer_master->sum('transfer_amount');
            
            $queryUpiFood = (new \yii\db\Query())->from('upi_transaction_master')
                   ->where('DATE(created_at)=CURDATE()')
                   ->andWhere(['voucher_type' => 'FOOD'])
                   ->andWhere(['status' => 'SUCCESS']);
            $sumUpiFood = $queryUpiFood->sum('amount');
            
            $queryOrderCount = (new \yii\db\Query())->from('order_detail')
                   ->where('DATE(created_at)=CURDATE()');
            $queryOrderCount = $queryOrderCount->count();
                      
            $queryUpiGift = (new \yii\db\Query())->from('upi_transaction_master')->where('DATE(created_at)=CURDATE()')
                   ->andWhere(['voucher_type' => 'GIFT'])
                   ->andWhere(['status' => 'SUCCESS']);                   
            $sumUpiGift = $queryUpiGift->sum('amount');            
           
            /*$queryUser = (new \yii\db\Query())->from('user')
                   ->where(['id' => $value['user_id']]);
            $queryUser=$queryUser->all();*/
            
           /* $queryEvent = (new \yii\db\Query())->from('event')
                   ->where(['id' => $value['id']]);
            $queryEvent=$queryEvent->all();*/

            $mailData=[];
            $temp=[];
            
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];

        $curl = curl_init();

        $dataArray = array("account_number"=>"462520303019005403","customer_id" =>"462515993434229934");
        

                    $urlData = http_build_query($dataArray);
                    $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_balance?'.$urlData,
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
                        "client_id: $client_id",
                        "client_secret: $client_secret",
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));
                    $response = curl_exec($curl);
                    $response=json_decode($response);            
                    
            /*$temp[0]['name']=$queryUser[0]['name'];
            $temp[0]['e_name']=$queryEvent[0]['title'];
            $temp[0]['e_date']=$queryEvent[0]['date'];*/
            $temp[0]['e_date']= $date->format('d-m-Y');
            
            $temp[0]['accBalance']=(isset($response->presentBalance)) ? $response->presentBalance : 0;
            $temp[0]['transfer']=(isset($transfer_master)) ? $transfer_master : 0;
            
            $temp[0]['sumRedeem']=(isset($sumRedeem)) ? $sumRedeem : 0;
            $temp[0]['sumUpiFood']=(isset($sumUpiFood)) ? $sumUpiFood : 0;
            $temp[0]['sumUpiGift']=(isset($sumUpiGift)) ? $sumUpiGift : 0;
            $temp[0]['orderCount']=(isset($queryOrderCount)) ? $queryOrderCount : 0;
            
            $temp[0]['eventCount']=$events;
            $mailData['mailData']=$temp;

            $appFromEmail="support@prezenty.in";
            //clement.dcruz@prezenty.in          
            $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/daily-summary-all' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo(['clement.dcruz@prezenty.in','saheed.aboobakar@prezenty.in'])->setSubject('Prezenty Day Summary')->setTextBody('Summary Details')->send();                                                                                                                        
              
            
          
        
          
    }
    
    public function actionVirtualAccountBalanceAll(){
        
        
        $mailData=[];
        $temp=[];
        $appFromEmail="support@prezenty.in";    
        
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        
        $accounts = BankAccount::find()
          ->where(['status'=> 1])
          ->all();        
        
        $i=0;
        foreach ($accounts as $key => $value) {
            
            
            if($value['user_id']){
         
                $curl = curl_init();
    
                $dataArray = array("account_number"=>$value['va_number'],"customer_id" =>$value['customer_id']);
            
                /*$dataArray = array("account_number"=>'462520436875944950',"customer_id" =>'saheed4uze-gmail-com-380');*/
    
                        $urlData = http_build_query($dataArray);
                        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_balance?'.$urlData,
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
                            "client_id: $client_id",
                            "client_secret: $client_secret",
                            "module_secret: $module_secret",
                            "provider_secret: $provider_secret"
                        ),
                    ));
                        $response = curl_exec($curl);
                        $response=json_decode($response);
                         $UserD=User::find()->where(['id'=> $value['user_id']])->one();   
                         
                         
                         $temp[$key]['name']=(isset($UserD->name)) ? $UserD->name : 0;;
                        $temp[$key]['va_number']=$value['va_number'];
                        $temp[$key]['balance']=$response;
                        
                        if(isset($response->presentBalance)){
                        
                        
                        
                        
                        
                        
                        /*    $i++;
                        } else {
                            $UserD=User::find()->where(['id'=> $value['user_id']])->one(); 
                            print_r($response);exit;
                        $temp[$key]['name']=$UserD->email;
                        $temp[$key]['va_number']=$value['va_number'];
                        $temp[$key]['balance']=5;
                        
                            
                        }*/
                        
                        
            }
            }
        }
        
        print_r($temp);exit;
        
        $mailData['mailData']=$temp;
        
            $appFromEmail="support@prezenty.in";
            //clement.dcruz@prezenty.in          
            /*['clement.dcruz@prezenty.in','saheed.aboobakar@prezenty.in']*/
            
            $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/virtual-account-balance' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Virtual Account Balance')->setTextBody('Prezenty Virtual Account Balance')->send(); 

    
    }
    
    public function actionGetStatement(){
        
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
        
        $mailData=[];
        $temp=[];
        
        $curl = curl_init();
         $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone) );

        curl_setopt_array($curl, array(
          CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_statement?from='.$date->format('Y-m-d').'&to='.$date->format('Y-m-d').'&account_number=462520303019005403&customer_id=462515993434229934',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            "client_id: $client_id",
            "client_secret: $client_secret",
            "module_secret: $module_secret",
            "provider_secret: $provider_secret"
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $response=json_decode($response);
        
        if(isset($response->statement)){
             
            
             $mailData['mailData']=$response->statement;
            
            
            //saheed.aboobakar@prezenty.in
            
            $appFromEmail="support@prezenty.in";
                    
            $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/virtual-account-statement' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Virtual Account Statement')->setTextBody('Prezenty Virtual Account Statement')->send();              
        }
        
        
    }
    
    public function actionAccountDetCoin()
    {
         $accounts = User::find()
          ->where(['status'=> 1,'role'=>'admin'])
          ->all();        
        $temp=array();
        $mailData=array();
        $i=0;
        
        foreach ($accounts as $key => $value) {
          
           $userid=$value['id'];
          $query ="SELECT ifnull((select sum(amount) from upi_transaction_master where status = 'SUCCESS' and voucher_type = 'GIFT' and ((user_id = $userid) or (event_id in (select id from event where user_id = $userid))) ),0)- ifnull((SELECT sum(amount) FROM redeem_master WHERE status = 'COMPLETE' and user_id = $userid),0) amount";
          
          
            $command = Yii::$app->db->createCommand($query);
            $result = $command->queryAll();
            $i=1;
            if($value['name'] && $result[0]['amount'] > 0 ){
                
                $temp[$key]['slno']=$i++; 
                $temp[$key]['name']=$value['name']; 
                $temp[$key]['amount']=$result[0]['amount'];
                
            }
           
            
        }
        
        
        $mailData['mailData']=$temp;
        $appFromEmail="support@prezenty.in";
            //clement.dcruz@prezenty.in          
        //'clement.dcruz@prezenty.in','saheed.aboobakar@prezenty.in'    
        //print_r('$location');exit;
        $this->renderPartial('@common/mail/coin-summary-all',$mailData);
        $mpdf=new mPDF();
        $mpdf->WriteHTML($this->renderPartial('@common/mail/coin-summary-all',$mailData));
        $email='saheed.aboobakar@prezenty.in';
        $location= Yii::$app->basePath.'/web/pdf/';
        
        $path = $mpdf->Output($location.'CoinSummaryAll.pdf', \Mpdf\Output\Destination::FILE);
        $mail = Yii::$app->mailer->compose()->attach($location.'CoinSummaryAll.pdf')->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Prezenty Coin Day Summary')->setTextBody('Coin Summary Details')->send();
        
       // $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/coin-summary-all' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo(['albinsunny1996@gmail.com'])->setSubject('Prezenty Coin Day Summary')->setTextBody('Coin Summary Details')->send();                                                                                                                        
                
        
    }
            
    public function actionBalanceSettlement()
    {    

        

        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];        
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];

        // $body=array(
        //     "baas_account_number" => "462520303019005403",
        //     "baas_account_ifsc" => "YESB0CMSNOC"
        // );
        
        
        $body=array(
            "baas_account_number" => "002267800000779",
            "baas_account_ifsc" => "YESB0000022"
        );


        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $baseUrlDecentro."v2/banking/account/virtual/balance_settlement",
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
            $response=json_decode($response); 
            curl_close($curl); 
            
            $model = new BalanceSettlement;

            $model->decentroTxnId=$response->decentroTxnId;
            $model->message=$response->message;
            $model->status=$response->status;
            $model->save(false);
            

    }    

    public function actionMoneyTransferInit(){

           // header('Access-Control-Allow-Origin: *');
            $ret=array();
            $amount=$this->getAmount();
            
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];            
            $curl = curl_init();
            $bank=array();
            $datetime=date("Y-m-d H-i-s");
            $reference_id=rand().'-Redeem-'.date("His");
    
            // $body=array(
            //         "reference_id" => $reference_id,
            //         "purpose_message"=> 'Transaction',
            //         "from_customer_id"=>"462515993434229934",
            //         "from_account"=> '462520303019005403',    
            //         "transfer_type"=> 'NEFT', 
            //         "mobile_number"=>"7025801819",
                    
            //         "to_account"=>"", 
                    
            //         "transfer_amount"=> $amount,
            //         "currency_code" => "INR",        
            //         "beneficiary_details"=> array(
            //             "payee_name"=>"",
            //             "email_address" => "", //'prezentyapp@gmail.com',
            //             "mobile_number"=> "", //'9544855856',
            //             "address"=> 'test',
            //             "ifsc_code"=> "", //'YESB0CMSNOC',
            //             "country_code"=> "IN",
            //             ),
                        
            //         // "transfer_datetime"=> $datetime,
            //         // "frequency"=> "weekly",
            //         // "end_datetime"=> date("Y-m-d H-i-s", strtotime('+1 hours')),
            //         );

//462515056346649420

            $body=array(
                "reference_id" => $reference_id,
                "purpose_message"=> 'Transaction',
                "from_customer_id"=>"462515993434229934",
                "from_account"=> '462520303019005403',    
                "transfer_type"=> 'IMPS', 
                "mobile_number"=>"7025801819",
                
                "to_account"=>"14690200010405", 
                
                "transfer_amount"=>strval($amount),
                "currency_code" => "INR",        
                "beneficiary_details"=> array(
                    "payee_name"=>"PREZENTY INFOTECH PRIVATE LIMITED",
                    "email_address" => 'prezentyapp@gmail.com',
                    "mobile_number"=> '9544855856',
                    "address"=> 'prezenty',
                    "ifsc_code"=> 'FDRL0001469',
                    "country_code"=> "IN",
                    ),
                );



//print_r(json_encode($body));exit;
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
            $response=json_decode($response); 
            curl_close($curl);
            
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


        } else {
            
                        $curl = curl_init();
                        
                     $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone) );
            
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_statement?from='.$date->format('Y-m-d').'&to='.$date->format('Y-m-d').'&account_number=462520303019005403&customer_id=462515993434229934',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',
                      CURLOPT_HTTPHEADER => array(
                        "client_id: $client_id",
                        "client_secret: $client_secret",
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                      ),
                    ));
                        
                        $response = curl_exec($curl);
                        $response=json_decode($response);
                        //print_r($response);exit;
                        if(isset($response->statement)){
                         
                        
                         $mailData['mailData']=$response->statement;
                        
                        
                        
                        $appFromEmail="support@prezenty.in";
                                
                        $mail = Yii::$app->mailer->compose([ 'html' => '@common/mail/virtual-account-statement' ] ,$mailData)->setFrom([$appFromEmail => 'Prezenty'])->setTo('saheed.aboobakar@prezenty.in')->setSubject('Prezenty Virtual Account Statement')->setTextBody('Prezenty Virtual Account Statement')->send();              
                    }            
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



        }


    }
    public function getAmount() {
    
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];

        $curl = curl_init();

        $dataArray = array("account_number"=>"462520303019005403","customer_id" =>"462515993434229934");

                    $urlData = http_build_query($dataArray);
                    $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_balance?'.$urlData,
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
                        "client_id: $client_id",
                        "client_secret: $client_secret",
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));
                    $response = curl_exec($curl);
                    $response=json_decode($response);    
                    
                    return $response->presentBalance;
    }    
        
}
