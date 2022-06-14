  
<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div>
	<h1>Dashboard</h1>
	<div class="row">
		<div class="col-lg-3 col-6">
			<!-- small box -->
			<div class="small-box bg-danger">
				<div class="inner">
					<h3><?= $uncleared; ?></h3>
					
					<p>Uncleared Transactions</p>
				</div>
				<div class="icon">
					<i class="ion ion-pie-graph"></i>
				</div>
        		<?= Html::a('More info <i class="fa fa-arrow-circle-right"></i>', ['/vendor/transactions'], ['class' => 'small-box-footer']) ?>
			</div>
		</div>
		
		<div class="col-lg-3 col-6">
			<!-- small box -->
			<div class="small-box bg-info">
				<div class="inner">
					<h3><?= $users; ?></h3>
					
					<p>Users</p>
				</div>
				<div class="icon">
					<i class="ion ion-bag"></i>
				</div>
        		<?= Html::a('More info <i class="fa fa-arrow-circle-right"></i>', ['/vendor/users'], ['class' => 'small-box-footer']) ?>
			</div>
		</div>
		
		<div class="col-lg-3 col-6">
			<!-- small box -->
			<div class="small-box bg-success">
				<div class="inner">
					<h3><?= ($total)?$total:'0'; ?></h3>

					<p>Total Voucher</p>
				</div>
				<div class="icon">
					<i class="ion ion-stats-bars"></i>
				</div>
        		<?= Html::a('More info <i class="fa fa-arrow-circle-right"></i>', ['/vendor/transactions', 'GiftVoucherTransactionSearch[from_date]' => date('Y-m-01'), 'GiftVoucherTransactionSearch[to_date]' => date("Y-m-t", strtotime(date('Y-m-d')))], ['class' => 'small-box-footer']) ?>
			</div>
		</div>
		
		<div class="col-lg-3 col-6">
			<!-- small box -->
			<div class="small-box bg-warning">
				<div class="inner">
					<h3><?= ($redeem)?$redeem:'0'; ?></h3>
					
					<p>Total Redemptions</p>
				</div>
				<div class="icon">
					<i class="ion ion-person-add"></i>
				</div>
        		<?= Html::a('More info <i class="fa fa-arrow-circle-right"></i>', ['/vendor/redeem'], ['class' => 'small-box-footer']) ?>
			</div>
		</div>
		
		
	</div>
</div>

<style>
.small-box {
    border-radius: .25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
}
.small-box .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
}
.bg-info {
    background-color: #17a2b8 !important;
}
.bg-success {
    background-color: #28a745 !important;
}
.bg-warning {
    background-color: #ffc107 !important;
}
.bg-danger {
    background-color: #dc3545 !important;
}
</style>