<?php

namespace backend\modules\vendor\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherTransaction;
use backend\models\GiftVoucherTransactionSearch;


class TransactionsController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new GiftVoucherTransactionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
    }

    public function actionClear($id, $status) {
      $model = GiftVoucherTransaction::findOne($id);
      $model->cleared = (int) !$status;
      $model->save();

      return $this->redirect('index');
    }

}
