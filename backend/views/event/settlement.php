<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model backend\models\Event */

$this->title ='Settlement Details';
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
        <?= Html::a('Settle Now', ['event/balance-settlement'], ['class' => 'btn btn-primary']) ?>
    </p>             
    <br>
    <br>
 <?= GridView::widget([
        'dataProvider' => $data,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'Settlement'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'Settlement-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'Settlement-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'Settlement-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'Settlement-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'Settlement-'.date('d-M-Y'),
                        'subject' =>'Settlement-'.date('d-M-Y'),
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

            'decentroTxnId',
            'status',
            'message',
            'created_at',
        ],
    ]); ?>

</div>
