<?php

namespace backend\modules\vendor\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherTransactionSearch;
use backend\models\GiftVoucherRedeem;
use backend\models\User;
use backend\models\GiftVoucherRedeemSearch;


class UsersController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new GiftVoucherTransactionSearch();
        $dataProvider = $searchModel->getTotal(Yii::$app->request->queryParams);

        return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
    }


    public function actionTransactions($user_id, $event_id) {
      $searchModel = new GiftVoucherRedeemSearch([
        'user_id' => $user_id,
		    'event_id' => $event_id,
      ]);
      $user = User::findOne($user_id);
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      return $this->render('transactions', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'user' => $user
      ]);
    }

    // Update redeemed amount
    public function actionUpdate($id) {
      $model = GiftVoucherRedeem::findOne($id);

      if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $model->save();
        Yii::$app->session->setFlash('success','Updated Successfully');

        return $this->redirect(['transactions', 'user_id' => $model->user_id, 'event_id' => $model->event_id]);
      }

      return $this->render('update_transaction', [
        'model' => $model
      ]);
    }

    // Delete redeemed amount
    public function actionDelete($id) {
      $model = GiftVoucherRedeem::findOne($id);
      $model->delete();
      Yii::$app->session->setFlash('success','Deleted Successfully');

      return $this->redirect(['transactions', 'user_id' => $model->user_id, 'event_id' => $model->event_id]);
    }

}
