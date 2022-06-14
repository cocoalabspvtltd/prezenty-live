<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\MenuGiftSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Menu Or Gifts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-gift-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Menu Or Gifts', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'meno-or-gifts'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'meno-or-gifts-'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'meno-or-gifts-'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'meno-or-gifts-'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'meno-or-gifts-'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'meno-or-gifts-'.date('d-M-Y'),
                        'subject' =>'meno-or-gifts-'.date('d-M-Y'),
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
            'price',
            'rating',
            [
                'attribute' => 'is_gift',
                'format' => 'raw',
                'value' => function($model) {
                    return ($model->is_gift == '1')?'Yes':'No';
                }
            ],
            [
                'attribute' => 'is_veg',
                'format' => 'raw',
                'value' => function($model) {
                    return ($model->is_veg == '1')?'Yes':'No';
                }
            ],
            [
                'attribute' => 'is_non_veg',
                'format' => 'raw',
                'value' => function($model) {
                    return ($model->is_non_veg == '1')?'Yes':'No';
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
