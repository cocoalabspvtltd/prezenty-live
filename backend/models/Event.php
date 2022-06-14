<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;
use common\models\EventFoodVoucher;

/**
 * This is the model class for table "event".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $date
 * @property string|null $time
 * @property string|null $image_url
 * @property string|null $music_file_url
 * @property int|null $gift_voucher_id
 * @property int|null $user_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class Event extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'time', 'created_at', 'modified_at'], 'safe'],
            [['image_url', 'music_file_url'], 'string'],
            [[ 'user_id', 'status'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public $from_date,$to_date,$is_favourite;
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'date' => 'Event Date',
            'time' => 'Event Time',
            'image_url' => 'Image',
            'music_file_url' => 'Music File',
            'user_id' => 'User',
            'created_by' =>'Created By',
            'status' => 'Status',
            'created_at' => 'Created Date Time',
            'modified_at' => 'Modified At',
            'gift_voucher_id' => 'Gift Vouchers',
            'menu_gift_id' => 'Menu Or Gifts',
            'va_number'=>'Account Number',
            'va_bank'=>'Bank',
            'va_upi'=>'UPI Id',
            'va_ifsc'=>'IFSC Code',
        ];
    }

    public function getEventUser() {
      return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getUser(){
        $model = User::find()->where(['id'=>$this->user_id])->one();
        return ($model)?$model->name:'-';
    }
    public function getUserMobile(){
        $model = User::find()->where(['id'=>$this->user_id])->one();
        return ($model)?$model->phone_number:'-';
    }        
    public function getUserEmail(){
        $model = User::find()->where(['id'=>$this->user_id])->one();
        return ($model)?$model->email:'-';
    }
    public function getOrganizer(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getImage(){
        $imagePath = $this->image_url;
        $locationPath = Yii::$app->params['base_path_event_images'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }

    public function getMusicFile(){
        $imagePath = $this->music_file_url;
        $locationPath = Yii::$app->params['base_path_music_files'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }

    public function getGiftVouchers(){
        $giftVouchers = EventGiftVoucher::find()
        ->leftJoin('gift_voucher','gift_voucher.id=event_gift_voucher.gift_voucher_id')
        ->where(['event_gift_voucher.status'=>1,'event_id'=>$this->id])->select('gift_voucher.title as title')->all();
        $array = [];
        foreach($giftVouchers as $gift){
            $array[] = $gift->title;
        }
        if(!empty($array[0])){

            return implode($array,', ');
        } else {

            return null;
        }    }

    public function getMenuGift(){
        $giftVouchers = EventMenuGift::find()
        ->leftJoin('menu_gift','menu_gift.id=event_menu_gift.menu_gift_id')
        ->where(['menu_gift.status'=>1,'event_id'=>$this->id])->select('menu_gift.title as title')->all();
        $array = [];
        foreach($giftVouchers as $gift){
            $array[] = $gift->title;
        }
        if(!empty($array[0])){

            return implode($array,', ');
        } else {

            return null;
        }
    }

    public function getTotalAmountReceived(){
        $model = EventGiftVoucherReceived::find()->where(['event_id'=>$this->id])->sum('amount');
        return $model;
    }

    public function getTotalAmountTransferred(){
        $model = GiftVoucherTransactions::find()->where(['event_id'=>$this->id])->sum('amount');
        return $model;
    }

    public function getFoodVouchers() {
      return $this->hasMany(EventFoodVoucher::className(), ['event_id' => 'id']);
    }

    public function getParticipants(){
      return $this->hasMany(EventParticipant::className(), ['event_id' => 'id']);
    }
}
