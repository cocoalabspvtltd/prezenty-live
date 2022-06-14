<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;


$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-update">

  <h1><?= Html::encode($this->title) ?></h1>

  <?php
    $form = ActiveForm::begin();

    foreach ($settings as $index => $setting) {
        echo $form->field($setting, "[$index]option_value")->label($setting->option_name);
    }

  ?>
  <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
