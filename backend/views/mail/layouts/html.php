<?php
use yii\helpers\Url;
$from = $this->params['from'];
$to = $this->params['to'];
$subject = $this->params['subject'];
$message = $this->params['message'];
?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Prezenty</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
          .cont * {
                max-width: 700px !important;
                word-wrap: break-word !important;
          }
        </style>
    </head>
    <body style="margin:0;padding:0;width:100%;">
      <div class="cont" style="width:700px;margin:auto;">
        <div style="background:#fafafa;width:100%;padding:25px 50px 0 50px;box-sizing:border-box;overflow:hidden;word-wrap:break-word">
			       <?= $message;?>
        </div>
       </div>
    </body>
</html>
