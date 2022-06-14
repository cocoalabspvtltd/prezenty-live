<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\FoodCouponBrandVoucher */

$this->title = Yii::t('app', 'Update Voucher: {name}', [
    'name' => $model->coupon_value,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vouchers'), 'url' => ['index', 'brand_id' => $model->brand_id]];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="food-coupon-brand-voucher-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
