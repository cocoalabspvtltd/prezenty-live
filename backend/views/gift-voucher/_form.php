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

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textArea() ?>

    <?= $form->field($model, 'image_url')->fileInput() ?>

    <?= $form->field($model, 'image_bg_url')->fileInput() ?>

    <label for="">Select Color</label>
    <?= $form->field($model, 'color_code', ['template' => "{input}"])->input('color',['class'=>"input_class"])?>

    <?= $form->field($model, 'vchr_terms_conditions')->textArea() ?>

    <h1>Voucher Admin Details</h1>

    <?= $form->field($modelUser,'name')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>

    <?= $form->field($modelUser,'email')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>

    <?= $form->field($modelUser, 'new_password')->passwordInput(['maxlength' => true, 'autocomplete'=>false]) ?>
    
    <?= $form->field($modelUser, 'confirm_password')->passwordInput(['maxlength' => true, 'autocomplete'=>false]) ?>

    <h1>Account Details</h1>

    <?= $form->field($model,'account_number')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>

    <?= $form->field($model,'account_name')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>
    
    <?= $form->field($model,'account_branch')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>
    
    <?= $form->field($model,'account_ifsc')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>
    
    <?= $form->field($model,'bank_name')->textInput(['maxlength' => true, 'autocomplete'=>false]) ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
