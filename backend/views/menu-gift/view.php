<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\MenuGift */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Menu Gifts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="menu-gift-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [

            'title',
            [
                'attribute' => 'image_url',
                'format' => 'raw',
                'value' => function($model) {
                    return $this->render('/common/image',['model'=>$model,'fieldName'=>'image_url']);
                }
            ],
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
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    return date('d M Y',strtotime($model->created_at));
                }
            ],
        ],
    ]) ?>

    <?php if($model->getItems()){ ?>
        <h1>Items</h1>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($model->getItems() as $item){ ?>
                    <tr>
                        <td><?=$item->title?></td>
                        <td><?=$item->price?></td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
    <?php }?>
    
    <p>
        <?= Html::a('back', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>
</div>
