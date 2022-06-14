<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Event Gift Vouchers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'event-gift-vouchers'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'event-gift-vouchers-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'event-gift-vouchers-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'event-gift-vouchers-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'event-gift-vouchers-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'event-gift-vouchers-'.date('d-M-Y'),
                        'subject' =>'event-gift-vouchers-'.date('d-M-Y'),
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

            'eventTitle',
            [
                'attribute' => 'user',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getUser($model->user);
                }
            ],
            [
                'attribute' => 'eventDate',
                'format' => 'raw',
                'value' => function($model){
                    return date('d M Y',strtotime($model->eventDate));
                }
            ],
            'giftTitle',
            [
                'attribute' =>'giftVoucherImage',
                'format' => 'raw',
                'value'=>function ($model) {
                    return '<img src="'.$model->getImage($model->giftVoucherImage).'" alt="Image" style="height:100px;width:100px;">';
                },
            ],

            ['class' => 'yii\grid\ActionColumn','template'=>'{view}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
