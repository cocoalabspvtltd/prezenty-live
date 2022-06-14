<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use backend\models\User;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'profile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $role = Yii::$app->user->identity->role;
        if($role == 'super-admin'){
            return $this->render('index');
        }elseif($role == 'voucher-admin'){
            return $this->redirect(['/vendor/dashboard']);
        }
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $role = Yii::$app->user->identity->role;
            if($role == 'super-admin'){
                return $this->redirect(['site/index']);
            } elseif($role == 'voucher-admin'){
                Yii::$app->session->set('vendor_id', $model->user->vendor->id);
				return $this->redirect(['/vendor/dashboard']);
            }
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionProfile(){
        $model = User::findOne(Yii::$app->user->identity->id);
        $image = $model->image_url;
        $post = Yii::$app->request->post();
        $model->setScenario('profile');
        if($post){
            if($model->load($post) && $model->validate()){
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

                if($model->new_password){
                    $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($model->new_password);
                }
                $model->save(false);
                yii::$app->session->setFlash('success','Profile Updated Successfully');
                return $this->redirect(['index']);
            }
        }
        return $this->render('profile',[
            'model' => $model
        ]);
    }

}
