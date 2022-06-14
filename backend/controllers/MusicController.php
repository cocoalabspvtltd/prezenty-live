<?php

namespace backend\controllers;

use Yii;
use backend\models\Music;
use backend\models\User;
use backend\models\MusicSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;

/**
 * MusicController implements the CRUD actions for Music model.
 */
class MusicController extends Controller
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
     * Lists all Music models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MusicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new Music model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Music();

        if ($model->load(Yii::$app->request->post())) {
            $musicFile = UploadedFile::getInstances($model,'music_file_url');
            if($musicFile){
                $model->music_file_url = 1;
            }
            if($model->music_file_url){
                $musicFile = UploadedFile::getInstances($model,'music_file_url');
                $imageLocation = Yii::$app->params['upload_path_music_files'];
                $modelUser = new User;
                $saveImage = $modelUser->uploadAndSave($musicFile,$imageLocation);
                if($saveImage){
                    $model->music_file_url = $saveImage;
                }
                $model->save(false);
                yii::$app->session->setFlash('success','Music file added successfully');
                return $this->redirect(['index']);
            }else{
                $model->addError('music_file_url','Music file cannot be blank');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Music model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $musicFile = UploadedFile::getInstances($model,'music_file_url');
            if($musicFile){
                $model->music_file_url = 1;
            }
            if($model->music_file_url){
                $musicFile = UploadedFile::getInstances($model,'music_file_url');
                $imageLocation = Yii::$app->params['upload_path_music_files'];
                $modelUser = new User;
                $saveImage = $modelUser->uploadAndSave($musicFile,$imageLocation);
                if($saveImage){
                    $model->music_file_url = $saveImage;
                }
                $model->save(false);
                yii::$app->session->setFlash('success','Music file updated successfully');
                return $this->redirect(['index']);
            }else{
                $model->addError('music_file_url','Music file cannot be blank');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Music model.
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

        yii::$app->session->setFlash('success','Music file deleted successfully');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Music model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Music the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Music::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
