<?php
namespace backend\modules\settings\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\EventOrderTaxSetting;

/**
 * Site controller
 */
class OrderTaxController extends Controller
{
  public function actionIndex()
    {
      $settings = EventOrderTaxSetting::find()->indexBy('id')->all();

      if (EventOrderTaxSetting::loadMultiple($settings, Yii::$app->request->post()) && EventOrderTaxSetting::validateMultiple($settings)) {

          foreach ($settings as $setting) {
              $setting->save(false);
          }

          return $this->redirect('order-tax');
      }

      return $this->render('form', [
        'settings' => $settings,
      ]);
    }
  }
