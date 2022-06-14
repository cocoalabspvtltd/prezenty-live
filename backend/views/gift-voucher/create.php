<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\GiftVoucher */

$this->title = 'Create Gift Voucher';
$this->params['breadcrumbs'][] = ['label' => 'Gift Vouchers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gift-voucher-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelUser' => $modelUser,
    ]) ?>

</div>
