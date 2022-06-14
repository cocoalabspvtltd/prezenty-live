<?php

namespace backend\controllers;

use Yii;
use backend\models\MenuGift;
use backend\models\MenuGiftItems;
use backend\models\User;
use backend\models\Event;
use backend\models\EventParticipant;
use backend\models\EventSearch;
use backend\models\MenuGiftSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;

/**
 * MenuGiftController implements the CRUD actions for MenuGift model.
 */
class MenuReportController extends Controller
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
     * Lists all MenuGift models.
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
    public function actionView($id){
        $query = EventParticipant::find()->where(['status'=>1,'event_id'=>$id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
        ]);
        $model = Event::find()->where(['id'=>$id])->one();
        return $this->render('view',[
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }
    public function actionChangeOrder(){
        $orderVal = $_POST['orderVal'];
        $user = $_POST['user'];

        $model = EventParticipant::find()->where(['id'=>$user])->one();
        $model->is_ordered = $orderVal;
        $model->save(false);
        return json_encode('success');
    }

    
    public function actionChangeDeliver(){
        $orderVal = $_POST['orderVal'];
        $user = $_POST['user'];

        $model = EventParticipant::find()->where(['id'=>$user])->one();
        $model->is_delivered = $orderVal;
        $model->save(false);
        return json_encode('success');
    }

    public function actionFund()
    {
        $searchModel = new EventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('events', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
