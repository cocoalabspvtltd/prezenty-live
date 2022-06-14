<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Event Participants';
$this->params['breadcrumbs'][] = ['label' => 'Event List', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Event Name: <?=$model->title?></h2>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'event-participants-'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'event-participants-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'event-participants-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'event-participants-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'event-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'event-participants-'.date('d-M-Y'),
                        'subject' =>'event-participants-'.date('d-M-Y'),
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
            'address',
            //'members_count',
          /*  [
                'attribute' => 'need_food',
                'format' => 'raw',
                'value' => function($model){
                    return ($model->need_food == 1)?'Yes':'No';
                }
            ],*/
            // [
            //     'attribute' => 'need_gift',
            //     'format' => 'raw',
            //     'value' => function($model){
            //         return ($model->need_gift == 1)?'Yes':'No';
            //     }
            // ],
/*            [
                'attribute' => 'is_veg',
                'format' => 'raw',
                'value' => function($model){
                    return ($model->is_veg == 1 && $model->need_gift == 0 && $model->need_food == 1)?'Yes':'No';
                }
            ],*/
/*            [
                'attribute' => 'is_veg',
                'label' => 'Is Non Veg',
                'format' => 'raw',
                'value' => function($model){
                    return ($model->is_veg == 0 && $model->need_gift == 0 && $model->need_food == 1)?'Yes':'No';
                }
            ],*/
/*            [
                'label' => 'Order Status',
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_ordered == 1){
                        return '<select class="order" data-id="'.$model->id.'"><option value="1">ORDERED</option><option value="0">PENDING</option></select>';
                    }else{
                        return '<select class="order" data-id="'.$model->id.'"><option value="0">PENDING</option><option value="1">ORDERED</option></select>';
                    }
                }
            ],
            [
                'label' => 'Delivery Status',
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_delivered == 1){
                        return '<select class="deliver" data-id="'.$model->id.'"><option value="1">DELIVERED</option><option value="0">PENDING</option></select>';
                    }else{
                        return '<select class="deliver" data-id="'.$model->id.'"><option value="0">PENDING</option><option value="1">DELIVERED</option></select>';
                    }
                }
            ],*/
/*            [
                'label' => 'Total Amount',
                'format' => 'raw',
                'value' => function($model){
                    return $model->getAmount();
                }
            ],*/
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php 
$url = Url::to(['menu-report/change-order']);
$deliverUrl = Url::to(['menu-report/change-deliver']);
$this->registerJs("
    $('.order').change(function(){
        var orderVal = $(this).val();
        var user = $(this).attr('data-id');
        $.ajax({
            url: '$url',
            data: {orderVal:orderVal,user:user},
            method: 'POST',
            success: function(r){
                location.reload();
            }
        });
    });
    $('.deliver').change(function(){
        var orderVal = $(this).val();
        var user = $(this).attr('data-id');
        $.ajax({
            url: '$deliverUrl',
            data: {orderVal:orderVal,user:user},
            method: 'POST',
            success: function(r){
                location.reload();
            }
        });
    });
");
