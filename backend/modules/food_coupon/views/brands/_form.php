<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\FoodCouponBrand */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="food-coupon-brand-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

     <?= $form->field($model, 'vchr_terms_conditions')->label('Terms and Condition')->textArea() ?>

    <?= $form->field($model, 'logo')
          ->fileInput([
          "accept"=>"image/*"
          ]) 
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
