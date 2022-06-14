<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model backend\models\Event */

$this->title ='Account Details';
$this->params['breadcrumbs'][] = ['label' => 'Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="event-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>
    <p style="float: right;">
        <?= Html::a('Sync', ['event/transaction-status','id' => $id], ['class' => 'btn btn-primary']) ?>
    </p>
    <?= DetailView::widget([
        'model' => $data,
        'attributes' => [
            'accountNumber',
            'presentBalance',
            'upiId',
            'transactionAmountLimit',
            'minimumBalance',
           
        ],
    ]) ?>
 <?= GridView::widget([
        'dataProvider' => $upi,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'event'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'event-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'event-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'event-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'event-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'event-'.date('d-M-Y'),
                        'subject' =>'event-'.date('d-M-Y'),
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

            'name',
            'amount',
            'status',
            'transaction_status_description',
        ],
    ]); ?>

</div>
