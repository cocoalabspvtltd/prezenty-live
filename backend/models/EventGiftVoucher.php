<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "event_gift_voucher".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $gift_voucher_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class EventGiftVoucher extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_gift_voucher';
    }
    public $title,$image_url,$eventTitle,$giftTitle,$eventDate,$giftVoucherImage,$user;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'gift_voucher_id', 'status'], 'integer'],
            [['created_at', 'modified_at','barcode'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_id' => 'Event ID',
            'gift_voucher_id' => 'Gift Voucher ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
    public function getImage($imagePath){
        $locationPath = Yii::$app->params['base_path_voucher_images'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }
    public function getUser($user){
        $model = User::find()->where(['id'=>$user])->one();
        return ($model)?$model->name:'-';
    }
    public function getGiftVoucher() {
      return $this->hasOne(GiftVoucher::class, ['id' => 'gift_voucher_id']);
    }
	
	  public function getEvent() {
      return $this->hasOne(Event::class, ['id' => 'event_id']);
    }
}
