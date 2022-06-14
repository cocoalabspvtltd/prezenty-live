<?php

use yii\helpers\Html;
use kartik\grid\GridView;

$this->title = "Transactions Report";
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="gift-voucher-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_search_transactions', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'gift-voucher'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'gift-voucher-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'gift-voucher-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'gift-voucher-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'gift-voucher-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'gift-voucher-'.date('d-M-Y'),
                        'subject' =>'gift-voucher-'.date('d-M-Y'),
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
            'trn_date:date',
            'amount',
            [
                'header' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->isCleared;
                }
              ],
              'event.title:text:Event'
        ],
    ]); ?>


</div>
