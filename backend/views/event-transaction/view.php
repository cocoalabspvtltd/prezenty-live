<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Received Gift Vouchers';
$this->params['breadcrumbs'][] = ['label' => 'Event Gift Vouchers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'users'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'users-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'users-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'users-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'users-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'users-'.date('d-M-Y'),
                        'subject' =>'users-'.date('d-M-Y'),
                        'keywords' => 'pdf, export, other, keywords, here'
                    ],
                ]
            ],
        ],
        'containerOptions' => ['style'=>'overflow: auto'],
        'toolbar' =>  [
            '{export}',
            '{toggleData}'
        ],
        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => false,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => true,
        'floatHeaderOptions' => ['scrollingTop' => 10],
        'showPageSummary' => true,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY
        ],
        'pager' => [
            'firstPageLabel' => 'First',
            'lastPageLabel'  => 'Last'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'event_participant_id',
                'label' => 'Participant name',
                'format' => 'raw',
                'value' => function($model){
                    return $model->getUser();
                }
            ],
            [
                'attribute' => 'event_id',
                'format' => 'raw',
                'value' => function($model){
                    return $model->getEventModel();
                }
            ],
            [
                'label' => 'User',
                'format' => 'raw',
                'value' => function($model){
                    return $model->getUserName($model->event_id);
                }
            ],
            [
                'label' => 'Gift Voucher',
                'format' => 'raw',
                'value' => function($model){
                    return $model->getGiftVoucher($model->event_gift_id);
                }
            ],
            'amount',
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('d M Y H:i:s',strtotime($model->created_at));
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<hr>

<?php 
$models = $dataProvider->getModels();
$totalAmount = 0;
foreach($models as $model){
    $totalAmount = $totalAmount + $model->amount;
}
$transferredAmount = $modelGiftVoucherTransactionsQuery->sum('amount');
$remainingAmount = $totalAmount - $transferredAmount;
?>
<h3>Total Received Amount: <?=$totalAmount?></h3>
<h3>Total Transferred Amount: <?=($transferredAmount)?$transferredAmount:0?></h3>
<h3>Amount Remains to Transfer: <?=$totalAmount - $transferredAmount?></h3>

<hr>
<h3>Transfer Amount To Vendor</h3>
<?php $form = ActiveForm::begin(); ?>

<input type="text" name="remainingAmount" value="<?=$remainingAmount?>" hidden>
<?= $form->field($modelGiftVoucherTransactions, 'amount')->textInput(['maxlength' => true]) ?>

<div class="form-group">
    <?= Html::submitButton('Send the Amount', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>