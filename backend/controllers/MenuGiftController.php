<?php

namespace backend\controllers;

use Yii;
use backend\models\MenuGift;
use backend\models\MenuGiftItems;
use backend\models\User;
use backend\models\MenuGiftSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;

/**
 * MenuGiftController implements the CRUD actions for MenuGift model.
 */
class MenuGiftController extends Controller
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
        $searchModel = new MenuGiftSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MenuGift model.
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
     * Creates a new MenuGift model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MenuGift();
        $params = Yii::$app->request->post();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $items = $params['MenuGift']['items'];
            $itemPrice = $params['MenuGift']['itemPrice'];
            $imageUrl = UploadedFile::getInstances($model,'image_url');
            if($imageUrl && !empty($imageUrl)){
                $imageLocation = Yii::$app->params['upload_path_menu_images'];
                $modelUser = new User;
                $saveImage = $modelUser->uploadAndSave($imageUrl,$imageLocation);
                if($saveImage){
                    $model->image_url = $saveImage;
                }
            }
            if($model->is_gift == 1){
                yii::$app->session->setFlash('success','Gift added successfully');
            }else{
                yii::$app->session->setFlash('success','Menu added successfully');
            }
            $model->save(false);
            if($items){
                foreach($items as $key => $item){
                    if($item){
                        $modelMenuGiftItems = new MenuGiftItems;
                        $modelMenuGiftItems->menu_gift_id = $model->id;
                        $modelMenuGiftItems->title = $item;
                        $modelMenuGiftItems->price = $itemPrice[$key];
                        $modelMenuGiftItems->save(false);
                    }
                }
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing MenuGift model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $params = Yii::$app->request->post();
        $image = $model->image_url;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $items = $params['MenuGift']['items'];
            $itemPrice = $params['MenuGift']['itemPrice'];
            $imageUrl = UploadedFile::getInstances($model,'image_url');
            if($imageUrl && !empty($imageUrl)){
                $imageLocation = Yii::$app->params['upload_path_menu_images'];
                $modelUser = new User;
                $saveImage = $modelUser->uploadAndSave($imageUrl,$imageLocation);
                if($saveImage){
                    $model->image_url = $saveImage;
                }
            }else{
                $model->image_url = $image;
            }
            if($model->is_gift == 1){
                yii::$app->session->setFlash('success','Gift updated successfully');
            }else{
                yii::$app->session->setFlash('success','Menu updated successfully');
            }
            $model->save(false);
            if($items){
                MenuGiftItems::deleteAll(['menu_gift_id'=>$model->id]);
                foreach($items as $key => $item){
                    if($item){
                        $modelMenuGiftItems = new MenuGiftItems;
                        $modelMenuGiftItems->menu_gift_id = $model->id;
                        $modelMenuGiftItems->title = $item;
                        $modelMenuGiftItems->price = $itemPrice[$key];
                        $modelMenuGiftItems->save(false);
                    }
                }
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing MenuGift model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MenuGift model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MenuGift the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MenuGift::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
