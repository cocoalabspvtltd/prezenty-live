<?php

namespace backend\controllers;

use Yii;
use backend\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->setScenario('create');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($model->new_password);
            $model->role = 'admin';
            $model->username = $model->email;
            $imageUrl = UploadedFile::getInstances($model,'image_url');
            if($imageUrl && !empty($imageUrl)){
                $imageLocation = Yii::$app->params['upload_path_profile_images'];
                $modelUser = new User;
                $saveImage = $modelUser->uploadAndSave($imageUrl,$imageLocation);
                if($saveImage){
                    $model->image_url = $saveImage;
                }
            }
            $model->save(false);
            yii::$app->session->setFlash('success','User Created Successfully');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->setScenario('update');
        $image = $model->image_url;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if($model->new_password){
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($model->new_password);
            }
            $model->username = $model->email;
            $model->role = 'admin';
            $imageUrl = UploadedFile::getInstances($model,'image_url');
            if($imageUrl && !empty($imageUrl)){
                $imageLocation = Yii::$app->params['upload_path_profile_images'];
                $modelUser = new User;
                $saveImage = $modelUser->uploadAndSave($imageUrl,$imageLocation);
                if($saveImage){
                    $model->image_url = $saveImage;
                }
            }else{
                $model->image_url = $image;
            }
            $model->save(false);
            yii::$app->session->setFlash('success','User Updated Successfully');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = 0;
        $model->save(false);

        yii::$app->session->setFlash('success','User Deleted Successfully');
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionContactUs()
    {

        $dataProvider = new ActiveDataProvider([
                                'query' => (new \yii\db\Query())
                                                ->select('tb1.*')
                                                ->from('contact_us as tb1')
                                                ->orderBy('tb1.id DESC')
            ]);
    
        return $this->render('contactus', [
            'dataProvider' => $dataProvider,
        ]);
    }      
}
