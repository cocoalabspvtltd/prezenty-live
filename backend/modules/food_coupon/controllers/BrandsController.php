<?php

namespace backend\modules\food_coupon\controllers;

use Yii;
use common\models\FoodCouponBrand;
use common\models\FoodCouponBrandSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * FoodCouponBrandsController implements the CRUD actions for FoodCouponBrand model.
 */
class BrandsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all FoodCouponBrand models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FoodCouponBrandSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single FoodCouponBrand model.
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
     * Creates a new FoodCouponBrand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FoodCouponBrand();
        $model->scenario = "create";

        if ($model->load(Yii::$app->request->post())) {
            $file = UploadedFile::getInstance($model, 'logo');
            $post=Yii::$app->request->post();            
            $model->vchr_terms_conditions=$post['FoodCouponBrand']['vchr_terms_conditions'];
            $model->logo = $file;
            if($model->save()) {
              $file->saveAs(Yii::getAlias('@uploads') . "/food-voucher-brands/" . $file);
              return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing FoodCouponBrand model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldFile = $model->logo;

        if ($model->load(Yii::$app->request->post())) {
            $file = UploadedFile::getInstance($model, 'logo');
            
            if($file) {
              $model->logo = $file;
            }
            if($model->save()) {
              if($file) {
                if($oldFile) {
                  unlink(Yii::getAlias('@uploads') . "/food-voucher-brands/" . $oldFile);
                }
                $file->saveAs(Yii::getAlias('@uploads') . "/food-voucher-brands/" . $file);
              }

              return $this->redirect(['index']);
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing FoodCouponBrand model.
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
     * Finds the FoodCouponBrand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FoodCouponBrand the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FoodCouponBrand::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
