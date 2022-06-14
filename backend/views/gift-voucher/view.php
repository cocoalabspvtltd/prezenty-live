<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\GiftVoucher */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Gift Vouchers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="gift-voucher-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'description',
            [
                'attribute' =>'image_url',
                'format' => 'raw',
                'value'=>function ($model) {
                    return $this->render('/common/image',['model'=>$model,'fieldName'=>'image_url']);
                },
            ],
            'color_code',
            [
                'label' =>'Username',
                'format' => 'raw',
                'value'=>function ($model) {
                    return $model->getUsername();
                },
            ],
            'account_number',
            'account_name',
            'account_ifsc',
            'account_branch',
            'bank_name'
        ],
    ]) ?>

</div>
