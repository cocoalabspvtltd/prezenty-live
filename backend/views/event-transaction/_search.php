<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use backend\models\User;
use backend\models\Event;
use backend\models\GiftVoucher;

$users = User::find()->where(['status'=>1])->andWhere(['role' => 'admin'])->all();
$userList = ArrayHelper::map($users,'id','name');
$events = Event::find()->where(['status'=>1])->all();
$eventList = ArrayHelper::map($events,'id','title');
$gifts = GiftVoucher::find()->where(['status'=>1])->all();
$giftsList = ArrayHelper::map($gifts,'id','title');
/* @var $this yii\web\View */
/* @var $model backend\models\EventSearch */
/* @var $form yii\widgets\ActiveForm */

$get = Yii::$app->request->get();
if($get){
    if($get['EventGiftVoucherSearch']['user']){
        $model->user = $get['EventGiftVoucherSearch']['user'];
    }
    if($get['EventGiftVoucherSearch']['eventTitle']){
        $model->eventTitle = $get['EventGiftVoucherSearch']['eventTitle'];
    }
    if($get['EventGiftVoucherSearch']['giftTitle']){
        $model->giftTitle = $get['EventGiftVoucherSearch']['giftTitle'];
    }
    if($get['EventGiftVoucherSearch']['eventDate']){
        $model->eventDate = $get['EventGiftVoucherSearch']['eventDate'];
    }
}
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
            <?= $form->field($model, 'eventTitle')->dropdownList($eventList,['prompt'=>'select...']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'user')->dropdownList($userList,['prompt'=>'select...']) ?>
        </div>
        <?php 
        if($model->eventDate){
            $model->eventDate = date('d-m-Y',strtotime($model->eventDate));
        }
        ?>
        <div class="col-md-3">
            <?php echo $form->field($model, 'eventDate')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Select Date...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-mm-yyyy'
                    ]
            ]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'giftTitle')->dropdownList($giftsList,['prompt'=>'select...']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-danger','style'=>'color: black']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
