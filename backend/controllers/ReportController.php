<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherRedeemSearch;
use backend\models\GiftVoucherTransaction;
use backend\models\GiftVoucherTransactionSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use backend\models\RzpPayments;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;
use backend\models\Products;
use Razorpay\Api\Api;
use linslin\yii2\curl;
use backend\models\RefundMaster;
use backend\models\AccountStatement;
use yii\data\ArrayDataProvider;
use backend\models\BankAccount;
use backend\models\User;

class ReportController extends Controller
{

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
    public function actionTransactions()
    {
        $searchModel = new GiftVoucherTransactionSearch([
          'showAll' => true
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('transactions', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
    }

    public function actionRedemptions()
    {
        $searchModel = new GiftVoucherRedeemSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('redemptions', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
   
        return $this->render('redemptions');
    }
    
    public function actionTransactionMaster(){
  
  
    $query1 = (new \yii\db\Query())
                ->select(['tb1.created_at as created_at','tb1.type as type',/*'tb2.name as product_name',*/'tb3.name as user_name','tb1.amount as Payed','tb1.id as id','tb3.name as pname'])
                ->from('rzp_payments as tb1')
                /*->innerJoin('products as tb2', 'tb2.id = tb1.product_id')*/
                ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
                ->orderBy(['tb1.id' => SORT_DESC]);

    $query2 = (new \yii\db\Query())
            ->select(['tb1.created_at as created_at','tb1.voucher_type as type',/*'tb2.name as product_name',*/'tb3.name as user_name','tb1.amount as Payed','tb1.id as id','tb4.name as pname'])
            ->from('upi_transaction_master as tb1')
            /*->innerJoin('products as tb2', 'tb2.id = tb1.id')*/
            ->leftJoin('user as tb3', 'tb3.id = tb1.user_id')
            ->leftJoin('event_participant as tb4', 'tb4.id = tb1.participant_id')
            ->orderBy(['tb1.id' => SORT_DESC]);

    $query3 = (new \yii\db\Query())
            ->select(['tb1.created_at as created_at','tb1.type as type','tb3.name as user_name','tb1.amount as Payed','tb1.id as id','tb3.name as pname'])
            ->from('redeem_master as tb1')
            ->innerJoin('user as tb3', 'tb3.id = tb1.user_id');            

    $unionQuery = (new \yii\db\Query())
        ->from(['dummy_name' => $query1->union($query2)]);
    $unionQuery = (new \yii\db\Query())
        ->from(['dummy_name' => $unionQuery->union($query3)])
        ->orderBy(['created_at' => SORT_DESC]);
    
       $dataProvider = new ActiveDataProvider([
                    'query' =>$unionQuery
                                     
        ]);
        
        //print_r($dataProvider);exit;
        return $this->render('rzr', [
                        'data' =>$dataProvider,
                    ]);  
        
        
    }
    
    public function actionDetailedView(){
        
        $get = Yii::$app->request->get();
        $id='';
        $type='';
        $dataProvider='';
        $view='';
        foreach ($get as $key => $value) {
            
            $id=$value['id'];
            $type=$value[0];
            
        }      

        
        if($type == 'BUY_VOUCHER'){
        
        
         $dataProvider = new ActiveDataProvider([
             'query' =>(new \yii\db\Query())
            ->select(['tb1.*','tb2.*','tb3.*','tb3.name as purchased_by','tb2.name as voucher','tb1.created_at as date_and_time','tb1.payment_id as tid'])
            ->from('rzp_payments as tb1')
            ->where(['tb1.id'=>$id,'tb1.type' => 'BUY_VOUCHER'])
            ->innerJoin('products as tb2', 'tb2.id = tb1.product_id')
            ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
            
            ]);    
        
        $view='buy-voucher';

            
        } else if($type == 'FOOD'){
            
        $dataProvider = new ActiveDataProvider([
             'query' =>(new \yii\db\Query())
            ->select(['tb1.*','tb2.*','tb3.*','tb3.name as order_by','tb2.title as event_name','tb2.id as event_id','tb1.created_at as date_and_time','tb1.status as order_status','tb1.id as tid'])
            ->from('upi_transaction_master as tb1')
            ->where(['tb1.id'=>$id,'tb1.voucher_type' => 'FOOD'])
            ->innerJoin('event as tb2', 'tb2.id = tb1.event_id')
            ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
            ]);    
        $view='upi-det';
            
        } else if($type == 'GIFT'){
            
        $dataProvider = new ActiveDataProvider([
             'query' =>(new \yii\db\Query())
            ->select(['tb1.*','tb2.*','tb3.*','tb3.name as order_by','tb2.title as event_name','tb2.id as event_id','tb1.created_at as date_and_time','tb1.status as order_status','tb1.id as tid'])
            ->from('upi_transaction_master as tb1')
            ->where(['tb1.id'=>$id,'tb1.voucher_type' => 'GIFT'])
            ->innerJoin('event as tb2', 'tb2.id = tb1.event_id')
            ->innerJoin('event_participant as tb3', 'tb3.id = tb1.participant_id')
            ]);    
        $view='upi-det';
            
        } else {
            
        $dataProvider = new ActiveDataProvider([
             'query' =>(new \yii\db\Query())
            ->select(['tb1.*','tb2.*','tb3.*','tb3.name as redeemed_by','tb2.title as event_name','tb2.id as event_id','tb1.created_at as date_and_time','tb1.status as redeem_status','tb1.id as tid'])
            ->from('redeem_master as tb1')
            ->where(['tb1.id'=>$id,'tb1.type' => 'REDEEM'])
            ->innerJoin('event as tb2', 'tb2.id = tb1.event_id')
            ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
            
            ]);    
        $view='redeem-det';         
            
        }


        return $this->render($view, ['data' =>$dataProvider,
        ]);         
        
        
    }
    
    
    public function actionRazorpayFailed(){
  
  
    $dataProvider = new ActiveDataProvider([
        'query' => (new \yii\db\Query())
               ->select(['tb1.created_at as created_at','tb1.type as type','tb4.name as product_name','tb3.name as user_name','tb1.amount as Payed','tb1.id as tid','tb1.payment_id as ref_no'])
                ->from('rzp_payments as tb1')
                 ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
                 ->innerJoin('products as tb4', 'tb4.id = tb1.product_id')
                ->leftJoin('order_master as tb2', 'tb1.payment_id = tb2.rzp_payment_id')
                ->where(['is', 'tb2.rzp_payment_id', null])
                ->andWhere(['!=','tb1.refund', 'YES'])
                ->orWhere(['is','tb1.refund', NULL])
                ->orderBy(['tb1.id' => SORT_DESC]),
    ]);
    
/*    $dataProvider = new ActiveDataProvider([
        'query' => (new \yii\db\Query())
                ->select(['tb2.payment_id as tid'])
                ->from('order_master as tb1')
                ->leftJoin('rzp_payments as tb2', 'tb2.payment_id = tb1.rzp_payment_id')
                ->where(['is', 'tb1.rzp_payment_id', null]),
    ]);  */  
    
    return $this->render('rzr-failed', [
                        'data' =>$dataProvider,
    ]);      
    

        
    }    

    
    public function actionRefundRequest(){
        
        $get = Yii::$app->request->get();
        $id='';
        $type='';
        foreach ($get as $key => $value) {
            
            $id=$value['id'];
            $type=$value[0];
            
        } 
        
        $rzr_pay = RzpPayments::find()->where(['id'=>$id,'type'=>$type])->one();


        $curl = curl_init();
        $amount=$rzr_pay->amount*100;
        $body=array('amount' =>  (int) $amount );
/*        print_r($amount);exit;*/
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.razorpay.com/v1/payments/'.$rzr_pay->payment_id.'/refund',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($body),
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic cnpwX2xpdmVfa2VidlFWenF4Z3dMQjc6VDhzc1VNZUtjNEZaZk0zbUNHWE5XSjRN',
            'Content-Type: application/json'
          ),          
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $response=json_decode($response);
        
        if(isset($response->id)){
            
             $rzr_pay = RzpPayments::find()->where(['id'=>$id,'type'=>$type])->one();

             
             $refundMaster = new RefundMaster;
             $refundMaster->payment_id=$rzr_pay->payment_id;
             $refundMaster->user_id=$rzr_pay->user_id;
             $refundMaster->product_id=$rzr_pay->product_id;
             $refundMaster->amount=$rzr_pay->amount;
             $refundMaster->type=$rzr_pay->type;
             $refundMaster->state=$rzr_pay->state;
             $refundMaster->refund=$rzr_pay->refund;
             $refundMaster->refund_id=$rzr_pay->refund_id;
             $refundMaster->response_body=json_encode($response);
             $refundMaster->save();
             
            $rzr_pay->refund='YES';
            $rzr_pay->refund_id=$response->id;
            $rzr_pay->refund_at=date('d-m-y h:i:s');
            $rzr_pay->save();
            
            yii::$app->session->setFlash('success','Refund Successfully');
            
        } else {
            
             $rzr_pay = RzpPayments::find()->where(['id'=>$id,'type'=>$type])->one();

             
             $refundMaster = new RefundMaster;
             $refundMaster->payment_id=$rzr_pay->payment_id;
             $refundMaster->user_id=$rzr_pay->user_id;
             $refundMaster->product_id=$rzr_pay->product_id;
             $refundMaster->amount=$rzr_pay->amount;
             $refundMaster->type=$rzr_pay->type;
             $refundMaster->state=$rzr_pay->state;
             $refundMaster->refund='NO';
             $refundMaster->refund_id=$rzr_pay->refund_id;
             $refundMaster->response_body=json_encode($response);
             $refundMaster->save();
             
            $rzr_pay->refund='NO';
            
            $rzr_pay->save();
            
            yii::$app->session->setFlash('error','Refund UnSuccessfull');
        } 
            
        $dataProvider = new ActiveDataProvider([
            'query' => (new \yii\db\Query())
                   ->select(['tb1.created_at as created_at','tb1.type as type','tb4.name as product_name','tb3.name as user_name','tb1.amount as Payed','tb1.id as tid','tb1.payment_id as ref_no'])
                    ->from('rzp_payments as tb1')
                     ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
                     ->innerJoin('products as tb4', 'tb4.id = tb1.product_id')
                    ->leftJoin('order_master as tb2', 'tb1.payment_id = tb2.rzp_payment_id')
                ->where(['is', 'tb2.rzp_payment_id', null])
                ->andWhere(['!=','tb1.refund', 'YES'])
                ->orWhere(['is','tb1.refund', NULL])
                ->orderBy(['tb1.id' => SORT_DESC]),
        ]); 
         return $this->render('rzr-failed', [
                            'data' =>$dataProvider,
        ]);
        
    }    

    public function actionRefundList(){
        
        $dataProvider = new ActiveDataProvider([
            'query' => (new \yii\db\Query())
                   ->select(['tb1.created_at as created_at','tb1.type as type','tb4.name as product_name','tb3.name as user_name','tb1.amount as Payed','tb1.id as tid','tb1.refund_id as ref_no'])
                    ->from('refund_master as tb1')
                     ->innerJoin('user as tb3', 'tb3.id = tb1.user_id')
                     ->innerJoin('products as tb4', 'tb4.id = tb1.product_id')
                    ->leftJoin('order_master as tb2', 'tb1.payment_id = tb2.rzp_payment_id')
                ->where(['tb1.refund'=> 'YES'])
                ->orderBy(['tb1.id' => SORT_DESC]),
        ]); 
         return $this->render('rzr-refund', [
                            'data' =>$dataProvider,
        ]);        
        
    }

    public function actionAccountStatement(){
        
        
        $client_id=Yii::$app->params['decentroConfig']['client_id'];
        $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
        $module_secret=Yii::$app->params['decentroConfig']['account_module'];
        $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
        $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];     
        
        $searchModel=new AccountStatement;   
        
        $curl = curl_init();
        $dateFrom='';
        $dateTo='';
        $get = Yii::$app->request->get();
        if(!empty($get)){
            

            
            $dateFrom =$get['fromDate'];
            $dateTo = $get['toDate'];
            
        } else {
            
            $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone)
            );
            
            $dateFrom = $date->format('Y-m-d');
            $dateTo = $date->format('Y-m-d');
           
        }    
         


        curl_setopt_array($curl, array(
          CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_statement?from='.$dateFrom.'&to='.$dateTo.'&account_number=462520303019005403&customer_id=462515993434229934',
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
        $dataProvider=array();
        
        $data=isset($response->statement) ? $response->statement : [];
        if(isset($response->statement)){
            
            //$dataProvider=new ActiveDataProvider($response->statement);
            //print_r($response->statement);exit;
             foreach ($response->statement as $key => $value) {
                if($value->type == 'CREDIT'){
                
                 if($value->senderAccountNumber == '462520882931723324'){
                     $value->acc_name='Prezenty Virtual Master';
                 } else {
                
                 $accounts = BankAccount::find()
                    ->where(['va_number'=> $value->senderAccountNumber])
                    ->one();
                $modelUser = User::find()->where(['id'=>$accounts->user_id])->one();                    
                 $value->acc_name=$modelUser->name;                         
                 }
                
                
                    
                } else {
                    
                 if($value->recieverAccountNumber == '14690200010405'){
                     
                     $value->acc_name='Prezenty Federal Account';
                     
                 } else {
                
                    $accounts = BankAccount::find()
                        ->where(['va_number'=> $value->recieverAccountNumber])
                        ->one();
                    $modelUser = User::find()->where(['id'=>$accounts->user_id])->one();                    
                    $value->acc_name=$modelUser->name;                         
                 }                    
                    
                }
                 
             }
                       
            
            
            $dataProvider = new ArrayDataProvider([
                'allModels' => $response->statement,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            
        } else {
            
            
            $dataProvider = new ArrayDataProvider([
                'allModels' => [],
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);            
        }
        
       // print_r($data);exit;
        
         return $this->render('account-statement', [
                            'dataProvider' =>$dataProvider,
                            
        ]);        
        
    }
}
