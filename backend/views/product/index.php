<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GiftVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Food/Gift Vouchers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gift-voucher-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Sync Products', ['product-sync'], ['class' => 'btn btn-success']) ?>
    </p>
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

            [
                'attribute' =>'Category Id',
                'format' => 'raw',
                'value'=>'category_id',
            ],

            [
                'attribute' =>'Product Name',
                'format' => 'raw',
                'value'=>'name',
            ],
            [
                'attribute' =>'SKU',
                'format' => 'raw',
                'value'=>'sku',
            ],
            [
                'attribute' =>'Min Price',
                'format' => 'raw',
                'value'=>'min_price',
            ],
            [
                'attribute' =>'Max Price',
                'format' => 'raw',
                'value'=>'max_price',
            ],
            [
                'attribute' =>'Voucher Type',
                'format' => 'raw',
                'attribute' => 'voucher_type',
                'value'=>function($data){
                     
                     if($data['voucher_type'] == 1){
                        
                        return 'Food';

                     } else if($data['voucher_type'] ==2){

                        return 'Gift';

                     } else {

                        return $data['voucher_type'];
                     }
                }
            ],
        [
            'format' => 'raw',
            'value' => function($data) {

                        return Html::a(

                            'Food',

                            Url::to(['product/is-food', 'id' => $data['id']]), 

                            [

                                'id'=>'grid-custom-button',

                                'data-pjax'=>true,

                                'action'=>Url::to(['product/is-food', 'id' => $data['id']]),

                                'class'=>'button btn btn-default',

                            ]

                        );

                }            
        ],
        [
            'format' => 'raw',
            'value' => function($data) {

                        return Html::a(

                            'Gift',

                            Url::to(['product/is-gift', 'id' => $data['id']]), 

                            [

                                'id'=>'grid-custom-button',

                                'data-pjax'=>true,

                                'action'=>Url::to(['product/is-gift', 'id' => $data['id']]),

                                'class'=>'button btn btn-default',

                            ]

                        );

                }            
        ],
        [
              'header' => 'Offer',
              'format' => 'raw',
              'value' => function($data){
                return Html::a("Update", ['product/offer', 'id' => $data['id']], ['data-pjax' => 0]);
              }
        ],        




            /*['class' => 'yii\grid\ActionColumn','template'=>'{view}'],*/
        ],
    ]); ?>


</div>
