<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use backend\models\User;

$users = User::find()->where(['status'=>1])->andWhere(['!=','role','super-admin'])->all();
$userList = ArrayHelper::map($users,'id','name');
/* @var $this yii\web\View */
/* @var $model backend\models\EventSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
.datepicker {
    top: 252.2px;
    left: 889.6px;
    z-index: 9999 !important;
    display: block;
}
</style>

<div class="event-search">

    <?php $form = ActiveForm::begin([
        'action' => ['account-statement'],
        'method' => 'post',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row">
<div class="event-form">

    <div class="col-md-3">
            <?php echo $form->field('from_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Select Date...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-mm-yyyy',
                        'endDate' => 'd'
                    ]
            ]); ?>
    </div>
    <div class="col-md-3">
            <?php echo $form->field('to_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Select Date...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-mm-yyyy',
                        'endDate' => 'd'
                    ]
            ]); ?>
    </div>    
    
    <?= $form->field($model, 'option_value')->textInput() ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

</div>

</div>
    <?php ActiveForm::end(); ?>
