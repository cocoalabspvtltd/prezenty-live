<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\db\Expression;

use backend\models\Event;

class EventReminderController extends Controller
{
    public function actionIndex()
    {
        $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone) );

        $events = Event::find()
          ->where(['>=', 'date', $date->format('Y-m-d')])
          ->andWhere(['>=', 'time', $date->format('H:i:s')])
          ->all();

        foreach($events as $event) {
          foreach($event->participants as $participant) {
            $payload = [
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'event_title' => $event->title,
            ];

            if($participant->email) {
              $date = Yii::$app->formatter->asDate($event->date, "MMM dd, yyyy");
              $time = Yii::$app->formatter->asTime($event->time, "h:m a");

              $message = $event->title . " event will be on {$date} at {$time}, Don't miss it";

              Yii::$app->notification->sendToOneUser(
                $message,
                $participant->email,
                'message',
                $message,
                false,
                $payload
              );
            }
          }
        }
        
        // echo Yii::$app->formatter->asTime('now', 'H:i:s') . "\n";
    }
}
