<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GiftVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Failed Woohoo Gift Vouchers';
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

/*            [
                'attribute' =>'Order Id',
                'format' => 'raw',
                'value'=>'order_id',
            ],*/
            [
                'attribute' =>'Invoice No',
                'format' => 'raw',
                'value'=>'inv_no_id',
            ],
            [
                'header' => 'Ref No',
                'format' => 'raw',
                'value' => function ($model) {
                    
                    if(isset($model['upi_transaction_id']) && !empty($model['upi_transaction_id'])){
                        
                        return $model['order_type'].'_'.$model['upi_transaction_id'];
                        
                    } else if(isset($model['rzp_payment_id']) && !empty($model['upi_transaction_id'])){
                        
                        return $model['order_type'].'_'.$model['rzp_payment_id'];
                        
                    } else if(isset($model['redeem_transaction_id']) && !empty($model['upi_transaction_id'])){
                        
                        return $model['order_type'].''.$model['redeem_transaction_id'];
                    }

                }
             ],             

            [
                'attribute' =>'Name',
                'format' => 'raw',
                'value'=>'order_by',
            ],
            [
                'attribute' =>'Email',
                'format' => 'raw',
                'value'=>'user_email',
            ],
            [
                'attribute' =>'Product',
                'format' => 'raw',
                'value'=>'product',
            ],
            [
                'attribute' =>'Amount',
                'format' => 'raw',
                'value'=>'amount',
            ],
/*            [
                'attribute' =>'Validity',
                'format' => 'raw',
                'value'=>'validity',
            ],  */                                  
            
            [
                'attribute' =>'Created Date Time',
                'format' => 'raw',
                'value'=>'created',
            ],    
            
            [
                'attribute' =>'Mobile',
                'format' => 'raw',
                'value'=>'phone_number',
            ],
            [
                'attribute' =>'Response',
                'format' => 'raw',
                'value'=>'message',
            ],
            [
                'attribute' =>'Status',
                'format' => 'raw',
                'value'=>'stas',
            ],
            [
              'header' => 'Action',
              'value' => function($data) {
                if(empty($data['ord_id']) && ($data['stas'] == 'ERROR' || $data['stas'] == 'FAILED'))  {
              	    
              	    return Html::a(Yii::t('app', ' {modelClass}', ['modelClass' => 'Create',]), ['product/re-create-init', 'id' => $data['oid']], ['class' => 'btn btn-success popupModal']);
                
                } else {
                    
                    return Html::a(Yii::t('app', ' {modelClass}', ['modelClass' => 'Sync',]), ['product/re-request-init', 'id' => $data['oid']], ['class' => 'btn btn-success popupModal']);                    
                    
                }
              },
              'format' => 'raw'
            ],             

            /*['class' => 'yii\grid\ActionColumn','template'=>'{view}'],*/
        ],
    ]); ?>


</div>
