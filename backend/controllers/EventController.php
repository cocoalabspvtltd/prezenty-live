<?php

namespace backend\controllers;

use Yii;
use backend\models\Event;
use backend\models\User;
use backend\models\EventSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use backend\models\UpiTransaction;
use yii\data\ActiveDataProvider;
use common\models\EventOrderInvoice;
use backend\models\BalanceSettlement;
use backend\models\TransferMaster;
use backend\models\TaxSettings;


/**
 * EventController implements the CRUD actions for Event model.
 */
class EventController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $role = (Yii::$app->user->isGuest)?'':Yii::$app->user->identity->role;
        if($role != 'super-admin'){
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        return parent::beforeAction($action);
    }
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST','GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','create','update','view','delete'],
                'rules' => [
                    [
                        'actions' => ['index','create','update','delete','view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function($rule, $action) {
                return $this->goHome();
                }
                ]
        ];
    }

    /**
     * Lists all Event models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Event model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Event model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Event the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Event::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionAccount($event_id)
    {
       
        $model = Event::find()->where(['id'=>$event_id])->one();

        $modelUser = User::find()->where(['id'=>$model->user_id])->one();
        $dataProvider='';
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
        $curl = curl_init();
        $name=str_replace(' ', '', $modelUser->name);
        $response=array();
        $response=array();
        if($model->va_number){
        $dataArray = array("account_number"=>$model->va_number,"customer_id" =>$name.''.$modelUser->id,"mobile_number" => $modelUser->phone_number);

                    $urlData = http_build_query($dataArray);

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
            }
                $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                                ->select(['tb1.*', 'tb2.name','tb2.phone'])
                                                ->from('upi_transaction_master as tb1')
                                                ->innerJoin('event_participant as tb2', 'tb1.participant_id = tb2.id')
                                                ->where(['tb1.event_id'=>$event_id,])
                                                ->andWhere(['not', ['tb1.participant_id' => null]]),
                 ]);            
                

                    return $this->render('account', [
                        'data' => $response,
                        'upi' =>$dataProvider,
                        'id' =>$event_id,
                     ]);
               
                


    }
    
    public function actionTransactionStatus($id)
    {
        $model = UpiTransaction::find()->where(['event_id'=>$id])->all();        
        if($model){

        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['module_secret'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
        foreach ($model as $key => $value) {
            
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $baseUrlDecentro."v2/payments/transaction/".$value->decentroTxnId."/status",
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
            curl_close($curl);
            $date = date('Y-m-d H:i:s');
            $dateTimeObject1 = date_create($value->created_at); 
            $dateTimeObject2 = date_create($date); 
              
            // Calculating the difference between DateTime objects
            $interval = date_diff($dateTimeObject1, $dateTimeObject2); 
              
            $minutes = $interval->days * 24 * 60;
            $minutes += $interval->h * 60;
            $minutes += $interval->i;           
            if($minutes > 10){

                $model = UpiTransaction::find()->where(['id'=>$value->id])->one();

                if($model->status != 'SUCCESS'){
                   // print_r($response->data->transactionStatus);exit;
                   if(isset($response->data)){
                    $model->status=$response->data->transactionStatus;
                    $model->transaction_status_description=$response->data->transactionStatusDescription;
                    $model->transaction_status_description=$response->data->bankReferenceNumber;
                    $model->npci_txnId=$response->data->npciTxnId;
                    $model->save(false);
                   }

                    $invoice = new EventOrderInvoice();

                        if($model->voucher_type == "FOOD"){

                              //$EventOrder = EventOrder::find()->where(['event_id'=>$model->event_id])->one();
                                


                        }
                } else {

                    $model->status=$response->data->transactionStatus;
                    $model->transaction_status_description=$response->data->transactionStatusDescription;
                    $model->transaction_status_description=$response->data->bankReferenceNumber;
                    $model->npci_txnId=$response->data->npciTxnId;
                    $model->save(false);                                                       

                } 

            } else {
                if(isset($response->data)){
                    
                    if($response->data->transactionStatus == "SUCCESS"){

                    $model = UpiTransaction::find()->where(['id'=>$value->id])->one();
                    $model->status=$response->data->transactionStatus;
                    $model->transaction_status_description=$response->data->transactionStatusDescription;
                    $model->transaction_status_description=$response->data->bankReferenceNumber;
                    $model->npci_txnId=$response->data->npciTxnId;
                    $model->save(false);
                                

                    }
                }

                }          
            }
                return $this->redirect(['event/account','event_id' => $id]); 

        } else {

           return $this->redirect(['event/account','event_id' => $id]); 

        }
    }
    public function actionBalanceSettlementView()
    {       

        $dataProvider = new ActiveDataProvider([
                'query' => (new \yii\db\Query())
                                ->select('tb1.*')
                                ->from('balance_settlement as tb1'),
        ]);

        return $this->render('settlement', [                        
                        'data' =>$dataProvider,
                     ]); 
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
            
            return $this->redirect('balance-settlement-view');

    }
    
    public function actionMoneyTransfer()
    {  
        
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
                    $responseView=json_decode($response);
                     
                    //print_r($response);exit;
                     
                    $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                                ->select('tb1.*')
                                                ->from('transfer_master as tb1')
                                                ->orderBy([
                                                  'tb1.id' => SORT_DESC      
                                                ])
                                                ,
            ]);
        
           return $this->render('money-transfer', [
                        'data' => $responseView,
                        'upi' =>$dataProvider,
                        
                     ]);        

    }
  
    public function actionMoneyTransferInit(){

            header('Access-Control-Allow-Origin: *');
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

            $ret = [
                        'message' => $response->error->message,
                        'statusCode' => 200,
                        'status' => $response->error->status,
                        'success' => false,
                        'id'      => $model->id,                  
                    ]; 

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

            $ret = [
                        'message' => 'transfer initiated', //$response->message,
                        'statusCode' => 200,
                        'success' => true,
                        'status' =>  $response->status,
                        'id' => $model->id,                     
                    ];

        }

        return $this->redirect('money-transfer'); 

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
    
    public function actionTaxSettings(){
        
        $dataProvider = new ActiveDataProvider([
                    'query' => (new \yii\db\Query())
                                    ->select('tb1.*')
                                    ->from('event_order_tax_settings as tb1'),
        ]);
        return $this->render('tax-settings', [
                        'data' =>$dataProvider,
                    ]);         
    }
    
    public function actionUpdateTax($id=null){
    
        
        $post = Yii::$app->request->post();
        if($post){
            $post=$post['TaxSettings'];
      
            $model = TaxSettings::find()->where(['id'=>$id])->one();
            $model->option_name=$post['option_name'];
            $model->option_value=$post['option_value'];
            $model->save(false);
        
        $dataProvider = new ActiveDataProvider([
                    'query' => (new \yii\db\Query())
                                    ->select('tb1.*')
                                    ->from('event_order_tax_settings as tb1'),
        ]);
        
        return $this->render('tax-settings', [
                        'data' =>$dataProvider,
                    ]);             
            
        }else{
            
            $model = TaxSettings::find()->where(['id'=>$id])->one();
            return $this->render('tax-settings-view', [
                        'model' =>$model,
                    ]);
            
        }
        
    }
    
     public function actionTransferStatus($id){
         
            
            $model=TransferMaster::find()->where(['id'=>$id])->one();
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];

            $id=$model->decentroTxnId;
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_status?request_id=decentro_request_20x&decentro_txn_id='.$id.'',
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
            
            curl_close($curl);
            $head='error';
            $msg='Server Error! Please Check after sometine';
            $response=json_decode($response);
            if(isset($response->originalTransactionResponse->status)){
                
                if($response->originalTransactionResponse->status != $model->status ){
                    
                    $model=TransferMaster::find()->where(['id'=>$id])->one();
                    $model->status=$response->originalTransactionResponse->status;
                    $model->save(false);
                } 
                 if($response->originalTransactionResponse->status == 'success')
                {
                    $head='success';
                    $msg=$response->originalTransactionResponse->status;
                } else {
                    
                    $msg='Your Transaction is on '.$response->originalTransactionResponse->status;
                }
            }
             yii::$app->session->setFlash($head,$msg);
             return $this->redirect('money-transfer'); 
     }
    
    
    
    
    
}
