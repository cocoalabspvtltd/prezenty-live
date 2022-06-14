<?php

namespace backend\modules\events\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;

use backend\models\EventParticipant;
use backend\modules\events\models\ParticipantSearch;

class ParticipantsController extends Controller
{
    public function actionIndex($event_id)
    {
      
      $searchModel = new ParticipantSearch([
        'event_id' => $event_id,
        'delivery_status' => 0,
        'payment_status' => 1
      ]);
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider
        ]);
    }

    public function actionChangeDeliveryStatus($id, $status) {
        $model = EventParticipant::findOne($id);
        $model->delivery_status = $status;
        $model->save(false);

        return $this->redirect(["index", 'event_id' => $model->event_id]);
    }

}
