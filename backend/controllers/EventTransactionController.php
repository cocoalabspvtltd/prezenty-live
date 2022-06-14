<?php

namespace backend\controllers;

use Yii;
use backend\models\GiftVoucher;
use backend\models\User;
use backend\models\Event;
use backend\models\EventGiftVoucher;
use backend\models\EventGiftVoucherSearch;
use backend\models\GiftVoucherSearch;
use backend\models\GiftVoucherTransactions;
use backend\models\EventGiftVoucherReceived;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;

/**
 * GiftVoucherController implements the CRUD actions for GiftVoucher model.
 */
class EventTransactionController extends Controller
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
     * Lists all GiftVoucher models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EventGiftVoucherSearch();
        $dataProvider = $searchModel->searchEvents(Yii::$app->request->queryParams);
        // echo "<pre>";
        // print_r($dataProvider->getModels());
        // exit;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the GiftVoucher model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return GiftVoucher the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EventGiftVoucher::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionView($id){
        $model = $this->findModel($id);
        $modelGiftVoucherTransactions = new GiftVoucherTransactions;
        $query = EventGiftVoucherReceived::find()->where(['status'=>1,'event_gift_id'=>$model->gift_voucher_id,'event_id'=>$model->event_id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query, 
        ]);
        $modelGiftVoucherTransactionsQuery = GiftVoucherTransactions::find()->where(['status'=>1,'event_id'=>$model->event_id,'vendor_id'=>$model->gift_voucher_id]);
        $post = Yii::$app->request->post();
        if($post && $modelGiftVoucherTransactions->load($post) && $modelGiftVoucherTransactions->validate()){
            if($post['remainingAmount'] >= $modelGiftVoucherTransactions->amount){
                $modelGiftVoucherTransactions->event_id = $model->event_id;
                $modelGiftVoucherTransactions->vendor_id = $model->gift_voucher_id;
                $modelGiftVoucherTransactions->trn_date = date('Y-m-d');
                $modelGiftVoucherTransactions->save(false);

                $no = rand(0, 9999999);
                $rand = str_pad($no, 7, "0", STR_PAD_LEFT);
                $barcodeType = $rand.time();
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                file_put_contents('../../common/uploads/barcodes/'.$barcodeType.'.png', $generator->getBarcode($barcodeType, $generator::TYPE_CODE_128));
                $model->barcode = $barcodeType;
                $model->save(false);
                
                yii::$app->session->setFlash('success','Amount transferred Successfully');
                return $this->redirect(['view','id'=>$id]);
            }else{
                $modelGiftVoucherTransactions->addError('amount','No balance amount is there');
            }
        }
        return $this->render('view',[
            'model' => $model,
            'dataProvider' => $dataProvider,
            'modelGiftVoucherTransactionsQuery' => $modelGiftVoucherTransactionsQuery,
            'modelGiftVoucherTransactions' => $modelGiftVoucherTransactions
        ]);
    }
}
