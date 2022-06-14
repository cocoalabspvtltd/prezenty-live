<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Profile Update ';
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>


    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
    
    <?= $form->field($model, 'image_url')->fileInput() ?>
    
    <?= $form->field($model, 'new_password')->passwordInput(['autofocus' => true,'autocomplete'=>'off']) ?>
    
    <?= $form->field($model, 'confirm_password')->passwordInput(['autofocus' => true,'autocomplete'=>'off']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>