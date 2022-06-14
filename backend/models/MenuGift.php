<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "menu_gift".
 *
 * @property int $id
 * @property string|null $title
 * @property int $price
 * @property int $rating
 * @property int $is_gift
 * @property int $is_veg
 * @property int $is_non_veg
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class MenuGift extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_gift';
    }

    public $from_date,$to_date;
    /** 
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price', 'is_gift', 'is_veg', 'is_non_veg', 'status'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['title','rating','price','is_gift'],'required'],
            [['price'],'number'],
            [['rating'], 'number', 'numberPattern' => '/^\s*[-+]?[0-5]*[,]?[0-5]+([eE][-+]?[0-5]+)?\s*$/','message'=>'Number must be Less than 6'],
            [['rating'],'string','min'=>1,'max'=>1],
            [['image_url'],'file', 'maxFiles' => 1,'extensions' => 'png, jpg, jpeg'],
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
            'price' => 'Price',
            'rating' => 'Rating',
            'is_gift' => 'Is Gift',
            'is_veg' => 'Is Veg',
            'is_non_veg' => 'Is Non Veg',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'image_url' => 'Image'
        ];
    }

    public function getImage(){
        $imagePath = $this->image_url;
        $locationPath = Yii::$app->params['base_path_menu_images'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }

    public function getItems(){
        $items = MenuGiftItems::find()->where(['status'=>1,'menu_gift_id'=>$this->id])->all();
        return $items;
    }
}
