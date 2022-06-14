<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Account Statement';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

<h1><?= Html::encode($this->title) ?></h1><br>
<div class="row">
<form action="account-statement">
  <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
  <div class="col-md-3">
From Date:

<input type="date" name="fromDate"  required>
</div>

  <div class="col-md-3">
To Date:
<input type="date" name="toDate"  required>
</div>


<input type="submit" value="Submit">

</form>
<br>
<br>
<br>

 <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions'=>function($model){
            if($model->transferType == 'IMPS'){
                
                return ['class' => 'danger'];
            }
            
        },
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
            'acc_name',
            [
                'attribute' => 'Description',
                'format' => 'raw',
                'value' => function($model){
                        
                    return $model->description;
                    
                }
            ],
            [
                'attribute' => 'From Account',
                'format' => 'raw',
                'value' => function($model){
                
                   if($model->type =='CREDIT'){
                        
                        return $model->senderAccountNumber;    
                        
                    } else {
                        
                       return '462520303019005403(Master)';
                    }
                        
                    
                }
            ],
            [
                'attribute' => 'To Account',
                'format' => 'raw',
                'value' => function($model){
                
                   if($model->type =='DEBIT'){
                        
                        return $model->recieverAccountNumber.'(Official)';    
                        
                    } else {
                        
                       return '462520303019005403(Master)';
                    }
                        
                    
                }
            ],            
            [
                'attribute' => 'Transfer Type',
                'format' => 'raw',
                'value' => function($model){
                
                    return $model->transferType;    
                        
                    
                }
            ],
            
            [
                'attribute' => 'Transfer Type',
                'format' => 'raw',
                'value' => function($model){
                
                    return $model->transferType;    
                        
                    
                }
            ],
            [
                'attribute' => 'Transaction Type',
                'format' => 'raw',
                'value' => function($model){
                
                    return $model->transactionType;    
                        
                    
                }
            ],              
            [
                'attribute' => 'Type',
                'format' => 'raw',
                'value' => function($model){
                
                    return $model->type;    
                        
                    
                }
            ],            
            [
                'attribute' => 'Amount',
                'format' => 'raw',
                'value' => function($model){
                    if($model->type =='DEBIT'){
                        
                        return $model->withdrawalAmount;    
                        
                    } else {
                        
                        return $model->depositAmount;
                    }
                    
                }
            ],
        ],
    ]); ?>