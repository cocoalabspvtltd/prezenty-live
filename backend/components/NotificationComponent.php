<?php
namespace backend\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
class NotificationComponent extends Component
{
    public function sendToOneUser($title,$value,$type=null,$typeVal=null,$isGroupMessage=null,$dataArr=[]){
        
        $contents = [
            'en' => $title
        ];
        $data = [];
        $options=[];

        if($type){
            
            $data = [
                'type' => $type,
                'value' => $typeVal,
                'isGroupMessage' => $isGroupMessage,
                'id' => $dataArr['id'],
                'event_id' => (isset($dataArr['event_id']))? $dataArr['event_id'] : $dataArr['id'] ,
                'event_name' => (isset($dataArr['event_title']))? $dataArr['event_title'] : '',
                'sender_email' =>(isset($dataArr['sender_email']))? $dataArr['sender_email'] : $value,
                'date' => $dataArr['date'],
                'time' => $dataArr['time']
            ];
            
            $options = [
                'filters' => array(array("field" => "tag", "key" => "user_id", "relation" => "=", "value" => $value)),
                'data' => $data,
            ];            
        } else {
            
            $options = [
            'filters' => array(array("field" => "tag", "key" => "user_id", "relation" => "=", "value" => $value))
        ];
            
        }
        
        $notification = Yii::$app->onesignal->notifications()->create($contents, $options);
                        // print_r($value);exit;
        $notificationWeb = Yii::$app->onesignalWeb->notifications()->create($contents, $options);
        return $notification;
    }
    public function sendToAllUsers($title,$type=null,$typeVal=null){
        $contents = [
            'en' => $title
        ];
        $data = [];
        if($type){
            $data = [
                'type' => $type,
                'value' => $typeVal
            ];
        }
        $options = [
            'included_segments' => array('All'),
            'data' => $data,
        ];
        $notification = Yii::$app->onesignal->notifications()->create($contents, $options);
        return $notification;
    }
    public function sendSummary($to,$mailData){
	    
	    $appFromEmail="support@prezenty.in";
        $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Week Summary')->setTextBody('Summary Details')->send();                                                                                                                        
        print_r($mail);exit;
	}      
    public function actionSendNotification(){
        $title = "testing from common component";
        $notification = Yii::$app->notification->sendToOneUser($title,'57','fundraiser','10');
        echo "<pre>";print_r($notification);exit;
    }
  
    
}