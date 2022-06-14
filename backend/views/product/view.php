<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GiftVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Order Status';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gift-voucher-index">

    <h1><?= Html::encode($this->title) ?></h1>
<?php if(!empty($dataProvider)) {?>
<h4>Card No:<?php echo $dataProvider->cards[0]->cardNumber ?></h4><br>
<h4>BarCode :<?php echo $dataProvider->cards[0]->barcode ?></h4>
<?php } else { ?>
<h4>Order Is Pending..</h4><br>
<?php  } ?>

</div>
