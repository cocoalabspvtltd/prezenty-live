<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\User;

$model = User::find()->where(['id'=>Yii::$app->user->identity->id])->one();
/* @var $this \yii\web\View */
/* @var $content string */
?>
<header class="main-header">
    <?= Html::a('<span class="logo-mini" style="font-size :10px">Prezenty</span><span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <!-- <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/> -->
                        <span class="hidden-xs"><?= $model->username?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <?php /*if($model->getImage()){ ?>
                                <img src="<?= $model->getImage()?>" class="img-circle" alt="User Image"/>
                            <?php }*/?>
                            <p>
                                <span style="font-size: 14px;"> Username : </span><?=$model->username?>
                            </p>
                            <p>
                                <span style="font-size: 14px;"> Name : </span><?=$model->name?>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?=Url::to(['/site/profile'])?>" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'Sign out',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
