<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'User-'.date('d-M-Y')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'User -'.date('d-M-Y')],
            GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'User -'.date('d-M-Y')],
            GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'User -'.date('d-M-Y')],
            GridView::PDF => [
                'filename' => 'User -'.date('d-M-Y'),
                'config' => [
                    'options' => [
                        'title' => 'User -'.date('d-M-Y'),
                        'subject' =>'User -'.date('d-M-Y'),
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
            'id',
            [
                'attribute' => 'email',
                'format' => 'raw',
                'value' => function ($model){
                    return ($model->email)?$model->email:'-';
                }
            ],
            'sales_person',
            'country_code',
            'created_at',
            [
                'attribute' => 'phone_number',
                'format' => 'raw',
                'value' => function ($model){
                    return ($model->phone_number)?$model->phone_number:'-';
                }
            ],
            [
                'attribute' => 'address',
                'format' => 'raw',
                'value' => function ($model){
                    return ($model->address)?$model->address:'-';
                }
            ],
            [
                'attribute' =>'image_url',
                'format' => 'raw',
                'value'=>function ($model) {
                    return $this->render('/common/image',['model'=>$model,'fieldName'=>'image_url']);
                },
            ],

            ['class' => 'yii\grid\ActionColumn','template'=>'{update} {delete}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
