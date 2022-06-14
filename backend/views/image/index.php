<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\MusicSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Music files';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="music-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Add Music File', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'music-files'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'music-files-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'music-files-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'music-files-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'music-files-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'music-files-'.date('d-M-Y'),
                        'subject' =>'music-files-'.date('d-M-Y'),
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
                'attribute' => 'image_file_url',
                'format' => 'raw',
                'value' => function ($model) {
                   
                   // return $this->render('/common/uploads/event-bg-images/',['model'=>$model->image_file_url]);
                   return $model->image_file_url;
                }
            ],

            ['class' => 'yii\grid\ActionColumn','template'=>'{update} {delete}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
