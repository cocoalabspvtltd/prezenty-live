<?php
namespace backend\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use frontend\models\TbMessage;
use frontend\models\TbPackage;
use backend\models\Person;
use backend\models\User;
use backend\models\Supplier;
use backend\models\Account;
use backend\models\DepartmentHead;
use backend\models\Bdm;
use backend\models\FundraiserScheme;
use jobs\models\TbFile;
class SummaryComponent extends Component
{
    
    public function sendSummary($fundraiserId=null,$to,$mailData){
	    
	    $appFromEmail="support@prezenty.in";
        $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo('albinsunny1996@gmail.com')->setSubject('Prezenty Week Summary')->setTextBody('Summary Details')->send();                                                                                                                        
        
	}
    
}