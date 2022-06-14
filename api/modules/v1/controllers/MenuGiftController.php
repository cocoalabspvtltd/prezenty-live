<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
// use backend\models\Log;
use api\modules\v1\models\Account;
use backend\models\MenuGift;
use backend\models\MenuGiftItems;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class MenuGiftController extends ActiveController
{
    public $modelClass = 'backend\models\MenuGift';    
      
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
        $query = MenuGift::find()->where(['status'=>1]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [$page, $per_page]
            ]
        ]);
        $list = [];
        foreach($dataProvider->getModels() as $model){
            $modelItems = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$model->id])->all();
            $menus = [
                'id' => $model->id,
                'title' => $model->title,
                'price' => $model->price,
                'rating' => $model->rating,
                'is_gift' => $model->is_gift,
                'is_veg' => $model->is_veg,
                'is_non_veg' => $model->is_non_veg,
                'image_url' => $model->image_url,
                'status' => $model->status,
                'created_at' => $model->created_at,
                'modified_at' => $model->modified_at,
                'items' => $modelItems,
            ];
            $list[] = $menus;
        }
        $hasNextPage = false;
        if(($page*$per_page) < ($query->count())){
            $hasNextPage = true;
        }
        $baseUrl = Yii::$app->params['base_path_menu_images'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'list' => $list,
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