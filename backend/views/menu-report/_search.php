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
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'title') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'user_id')->dropdownList($userList,['prompt'=>'select...']) ?>
        </div>
        <?php 
        if($model->from_date){
            $model->from_date = date('d-m-Y',strtotime($model->from_date));
        }
        if($model->to_date){
            $model->to_date = date('d-m-Y',strtotime($model->to_date));
        }
        if($model->date){
            $model->date = date('d-m-Y',strtotime($model->date));
        }
        ?>
        <div class="col-md-3">
            <?php echo $form->field($model, 'date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Select Date...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-mm-yyyy'
                    ]
            ]); ?>
        </div>
        <div class="col-md-3">
            <?php echo $form->field($model, 'from_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Select Date...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-mm-yyyy',
                        'endDate' => 'd'
                    ]
            ]); ?>
        </div>
        <div class="col-md-3">
            <?php echo $form->field($model, 'to_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Select Date...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-mm-yyyy',
                        'endDate' => 'd'
                    ]
            ]); ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-danger','style'=>'color: black']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
