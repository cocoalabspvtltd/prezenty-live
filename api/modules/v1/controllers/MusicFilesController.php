<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
// use backend\models\Log;
use api\modules\v1\models\Account;
use backend\models\Music;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class MusicFilesController extends ActiveController
{
    public $modelClass = 'backend\models\Music';    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }    
    public function  actionList(){
        header('Access-Control-Allow-Origin: *');
        $get = Yii::$app->request->get();
        $page = isset($get['page'])?$get['page']:1;
        $per_page = isset($get['per_page'])?$get['per_page']:20;
        $query = Music::find()->where(['status'=>1]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $baseUrl = Yii::$app->params['base_path_music_files'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'list' => $dataProvider,
            'page' => (int) $page,
            'perPage' => (int) $per_page,
            'hasNextPage' => $hasNextPage,
            'totalCount' => (int) $query->count(),
            'message' => 'Listed Successfully',
            'success' => true
        ];
        return $ret;
    }
}