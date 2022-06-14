<?php

namespace backend\modules\vendor\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherRedeemSearch;
use backend\models\GiftVoucherTransaction;
use backend\models\GiftVoucherTransactionSearch;

class ReportController extends Controller
{
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
        $searchModel = new GiftVoucherRedeemSearch([
          'vendor_id' => Yii::$app->session->get('vendor_id'),
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('redemptions', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
   
        return $this->render('redemptions');
    }

}
