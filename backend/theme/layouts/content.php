<?php
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;
use yii\web\View;
?>
<div class="content-wrapper">
    <section class="content-header">
        <?=
        Breadcrumbs::widget(
            [
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]
        ) ?>
    </section>

    <section class="content">
        <?php //= Alert::widget() ?>
        <?= $content ?>
    </section>
</div>
<?php if (Yii::$app->session->hasFlash('success')):
$this->registerJs(' alertMessage("Success","'.Yii::$app->session->getFlash('success') .'","success");
   ',View::POS_END);
 endif; ?>
 
 <?php if (Yii::$app->session->hasFlash('error')):
$this->registerJs(' alertMessage("Error","'.Yii::$app->session->getFlash('error') .'","error");
   ',View::POS_END);
 endif; ?>
