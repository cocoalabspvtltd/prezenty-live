<?php
// sendgrid

namespace common\components;

use Yii;

class Email {

  public function send($to, $subject, $params=[], $ccs=[], $bccs=[], $attachment = null)
  {
    $mailer = Yii::$app->sendGrid;

    $message = $mailer->compose('mail', $params)
      ->setFrom(['info@prezenty.in' => 'Prezenty'])
      ->setReplyTo('noreply@prezenty.in')
      ->setTo($to)
      ->setSubject($subject)
      // ->setHtmlBody('Dear -username-,<br><br>My HTML message here')
      // ->setTextBody('Dear -username-,\n\nMy Text message here')
      //->setTemplateId('1234')
      //->addSection('%section1%', 'This is my section1')
      //->addHeader('X-Track-UserType', 'admin')
      //->addHeader('X-Track-UID', Yii::$app->user->id)
      //->addCategory('tests')
      //->addCustomArg('test_arg', 'my custom arg')
      //->setSendAt(time() + (5 * 60))
      //->setBatchId(Yii::$app->mailer->createBatchId())
      //->setIpPoolName('7')
      ;

    if($attachment){
      $message->attach($attachment);
    }

    $response = $message->send();

    if ($response === true) {
        // echo 'Success!';
        // echo '<pre>' . print_r($mailer->getRawResponses(), true) . '</pre>';
    } else {
        echo 'Error!<br>';
        print_r($mailer->getErrors(), true);
    }
  }
}