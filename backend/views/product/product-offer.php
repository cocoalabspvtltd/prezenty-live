<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\GiftVoucher */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="gift-voucher-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <h1>Product Offer Details(%)</h1>

    <?= $form->field($modelProducts,'offers')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>