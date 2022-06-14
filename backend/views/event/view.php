<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Event */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="event-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getUser();
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
                'attribute' => 'image_url',
                'format' => 'raw',
                'value' => function ($model) {
                    return $this->render('/common/image',['model'=>$model,'fieldName'=>'image_url']);
                }
            ],
            [
                'attribute' => 'music_file_url',
                'format' => 'raw',
                'value' => function ($model) {
                    return $this->render('/common/music',['model'=>$model]);
                }
            ],
            [
                'attribute' => 'gift_voucher_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getGiftVouchers();
                }
            ],
            [
                'attribute' => 'menu_gift_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getMenuGift();
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('d M Y',strtotime($model->created_at));
                }
            ],
        ],
    ]) ?>

</div>
