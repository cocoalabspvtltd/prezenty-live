<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GiftVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Woohoo Gift Vouchers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gift-voucher-index">

    <h1><?= Html::encode($this->title) ?></h1>

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
                'attribute' =>'Order Id',
                'format' => 'raw',
                'value'=>'order_id',
            ],
            [
                'attribute' =>'Invoice No',
                'format' => 'raw',
                'value'=>'inv_no_id',
            ],            
            [
                'header' => 'Ref No',
                'format' => 'raw',
                'value' => function ($model) {
                    
                    if(isset($model['upi_transaction_id'])){
                        
                        return $model['order_type'].'_'.$model['upi_transaction_id'];
                        
                    } else if(isset($model['rzp_payment_id'])){
                        
                        return $model['order_type'].'_'.$model['rzp_payment_id'];
                        
                    } else if(isset($model['redeem_transaction_id'])){
                        
                        return $model['order_type'].''.$model['redeem_transaction_id'];
                    }

                }
             ],             
            [
                'attribute' =>'Name',
                'format' => 'raw',
                'value'=>'recipient_name',
            ],
            [
                'attribute' =>'Email',
                'format' => 'raw',
                'value'=>'recipient_email',
            ],
            [
                'attribute' =>'Product',
                'format' => 'raw',
                'value'=>'name',
            ],
            [
                'attribute' =>'Amount',
                'format' => 'raw',
                'value'=>'amount',
            ],
            [
                'attribute' =>'Validity',
                'format' => 'raw',
                'value'=>'validity',
            ],                                    
            
            [
                'attribute' =>'Created Date Time',
                'format' => 'raw',
                'value'=>'created',
            ],    
            
            [
                'attribute' =>'Mobile',
                'format' => 'raw',
                'value'=>'recipient_mobile',
            ],
            [
                'attribute' =>'Recipient Name',
                'format' => 'raw',
                'value'=>'recipient_name',
            ],
            [
                'attribute' =>'Status',
                'format' => 'raw',
                'value'=>'statusOrd',
            ],            
            [
              'header' => 'Action',
              'value' => function($data) {

                    return Html::a(Yii::t('app', ' {modelClass}', ['modelClass' => 'Send',]), ['product/re-send-card-det', 'id' => $data['ordId']], ['class' => 'btn btn-success popupModal']);                
               
              },
              'format' => 'raw'
            ], 

            
        ],
    ]); ?>


</div>
