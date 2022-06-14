<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use common\models\EventOrderInvoice;
use kartik\mpdf\Pdf;

/**
 * Site controller
 */
class InvoicesController extends Controller
{
  
    public function actionView($id)
    {
      $this->layout = 'pdf';

      $model = EventOrderInvoice::findOne($id);
      
      $content = $this->renderPartial('view', [
        'model' => $model,
      ]);
    
      $mpdf = new \Mpdf\Mpdf();
      $mpdf->WriteHTML($content);
      $mpdf->Output("Prezenty_Invoice_{$model->id}.pdf", "D");
    }
}
