<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "gift_voucher".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $image_url
 * @property string|null $expiry_date
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class GiftVoucher extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gift_voucher';
    }
    public $from_date,$to_date;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'modified_at','color_code','user_id','description'], 'safe'],
            [['status'], 'integer'],
            [['title', 'image_url','image_bg_url','account_number','account_name','account_branch','account_ifsc','bank_name'], 'string', 'max' => 255],
            [['title','account_number','account_name','account_branch','account_ifsc','bank_name'],'required'],
            [['image_url'],'file', 'maxFiles' => 1,'extensions' => 'png, jpg, jpeg'],
            [['image_bg_url'],'file', 'maxFiles' => 1,'extensions' => 'png, jpg, jpeg'],
            [['account_number'],'number']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'image_url' => 'Image',
            'image_bg_url' =>'Image Background',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'vchr_terms_conditions' => 'Terms And Conditions',
        ];
    }
    public function getImage(){
        $imagePath = $this->image_url;
        $locationPath = Yii::$app->params['base_path_voucher_images'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }
    public function getUsername(){
        $model = User::find()->where(['status'=>1,'id'=>$this->user_id])->one();
        return ($model)?$model->username:'';
    }
}
