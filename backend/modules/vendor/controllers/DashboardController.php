<?php

namespace backend\modules\vendor\controllers;

use Yii;
use yii\web\Controller;
use backend\models\GiftVoucherTransaction;
use backend\models\GiftVoucherRedeem;
use backend\models\User;

class DashboardController extends Controller
{
    public function actionIndex()
    {
        $uncleared = GiftVoucherTransaction::find()
                        ->select('*')
                        ->where(['vendor_id' => Yii::$app->session->get('vendor_id')])
                        ->andWhere('status = 1')
                        ->andWhere('cleared = 0')
                        ->count();

        $total = GiftVoucherTransaction::find()
                        ->select('*')
                        ->where(['vendor_id' => Yii::$app->session->get('vendor_id')])
                        ->andWhere('status = 1')
                        ->andWhere('cleared = 1')
                        ->sum('amount');

        $redeem = GiftVoucherRedeem::find()
                        ->select('*')
                        ->where(['vendor_id' => Yii::$app->session->get('vendor_id')])
                        ->andWhere('status = 1')
                        ->sum('amount');

        $users = User::find()
                ->leftJoin('event', 'event.user_id=user.id')
                ->leftJoin('gift_voucher_transactions', 'gift_voucher_transactions.event_id=event.id')
                ->where(['gift_voucher_transactions.vendor_id' => Yii::$app->session->get('vendor_id')])
                ->andWhere('gift_voucher_transactions.cleared = 1')
                ->groupBy('user.id')
                ->count();

        return $this->render('index', [
          'uncleared' => $uncleared,
          'total' => $total,
          'redeem' => $redeem,
          'users' => $users,
        ]);
    }

}