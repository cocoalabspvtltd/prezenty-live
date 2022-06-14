<?php

use yii\helpers\Html;
use kartik\grid\GridView;

$this->title = "Participants";
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="participants-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'participants'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'participants-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'participants-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'participants-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'participants-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'participants-'.date('d-M-Y'),
                        'subject' =>'participants-'.date('d-M-Y'),
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
            'email',
            'phone',
            'order.paymentStatus:raw:Payment Status',
            'deliveryStatus:raw:Delivery Status',
            [
              'header' => 'action',
              'format' => 'raw',
              'value' => function ($model) {
                if($model->delivery_status == 0) {
                  $text = "Mark as delivered";
                  $status = 1;
                } else {
                  $text = "Mark as UnDelivered";
                  $status = 0;
                }

                return Html::a($text, [
                  'participants/change-delivery-status',
                  'id' => $model->id,
                  'status' => $status
                ], [
                  'data-pjax' => 0,
                  'data-confirm' => 'Are you sure?',
                  'data-method' => 'post'
                ]);
              }
            ]
        ],
    ]); ?>


</div>
