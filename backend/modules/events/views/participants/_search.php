<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\GiftVoucherTransactionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="participants-search">

    <?php $form = ActiveForm::begin([
        'action' => ['search', 'event_id' => $model->event_id],
        'method' => 'get',
    ]); ?>

    <div class="row">
        
        
        <div class="col-md-3">
             <?= $form->field($model, 'delivery_status')->dropdownList([
               "Undelivered",
               "Delivered"
             ]) ?>
        </div>
<!--        <div class="col-md-3">
             <?= $form->field($model, 'payment_status')->dropdownList([
               "Unpaid",
               "Paid",
             ]) ?>
        </div>-->
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index', 'event_id' => $model->event_id], ['class' => 'btn btn-danger','style'=>'color: black']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
