
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model backend\models\Event */

$this->title ='Tax Settings';
$this->params['breadcrumbs'][] = ['label' => 'Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

\yii\bootstrap\Modal::begin(['id' =>'modal']);
 \yii\bootstrap\Modal::end();
 
?>
<div class="event-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

 <?= GridView::widget([
        'dataProvider' => $data,
        
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'tax'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'tax-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'tax-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'tax-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'tax-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'tax-'.date('d-M-Y'),
                        'subject' =>'tax-'.date('d-M-Y'),
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

            'option_name',
            'option_value',
            'created_at',
            'modified_at',
            [
              'header' => 'Comments',
              'value' => function($data) {
              	return Html::a(Yii::t('app', ' {modelClass}', ['modelClass' => 'update',]), ['event/update-tax', 'id' => $data['id']], ['class' => 'btn btn-success popupModal']);
              },
              'format' => 'raw'
            ],            
            
        ],
    ]); ?>

</div>