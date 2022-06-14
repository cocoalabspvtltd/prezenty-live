<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Events';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
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

            'title',
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getUser();
                }
            ],
            [
                'attribute' => 'phone_number',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getUsermobile();
                }
            ],
            [
                'attribute' => 'email',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getUserEmail();
                }
            ],             
            [
                'attribute' => 'date',
                'format' => 'raw',
                'value' => function($model){
                    return date('d M Y',strtotime($model->date));
                }
            ],
            [
                'attribute' => 'time',
                'format' => 'raw',
                'value' => function($model){
                    return date('h:i A',strtotime($model->time));
                }
            ],
            [
              'header' => 'Participants',
              'format' => 'raw',
              'value' => function($model){
                return Html::a("View", ['events/participants/index', 'event_id' => $model->id], ['data-pjax' => 0]);
              }
            ],
            ['class' => 'yii\grid\ActionColumn','template'=>'{view}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
