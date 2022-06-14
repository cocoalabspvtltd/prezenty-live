<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\GiftVoucher */

$this->title = 'Update Gift Voucher: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Gift Vouchers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="gift-voucher-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelUser' => $modelUser 
    ]) ?>

</div>
