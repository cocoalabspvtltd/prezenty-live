<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="event-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'mobile')->textInput(['maxlength' => true, 'id' => 'mobile']) ?>

    <?php if(Yii::$app->request->post()) { ?>
      <div class="panel">
        <table class='table table-bordered'>
          <tbody>
            <tr>
              <td>Customer</td>
              <td><?= $user->name ?></td>
            </tr>
			<tr>
              <td>Total Amount</td>
              <td><?= $total ?></td>
            </tr>
			<tr>
              <td>Redeemed Amount</td>
              <td><?= $redeemed ?></td>
            </tr>
            <tr>
              <td>Balance</td>
              <td><?= $balance ?></td>
            </tr>
            <tr>
              <td>ID</td>
              <td><?= ($user->id_copy)? Html::a("View", $user->IdCopy, ['target' => '_blank']) : ''; ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <?= $form->field($model, 'balance')->hiddenInput()->label(false) ?>

      <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>
      <?= $form->field($model, 'verify_method')->radioList($model->getVerificationMethods(), ['id' => 'verify_method']) ?>
      <div id="otp-div" class="<?php echo $model->verify_method == "otp" ? "" : "hide"; ?> row">
        <div class="col-md-4">
          <?= $form->field($model, 'otp')->textInput(['maxlength' => true])->label('OTP - <a href="#" id="generate_otp">Generate</a>') ?>
        </div>
        <!--<div class="col-md-4">
          <a href="#" id="generate_otp">Generate</a>
        </div>-->
      </div>
    <?php } ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success', 'name' => 'mobile']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>