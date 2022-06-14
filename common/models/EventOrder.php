<?php

namespace common\models;

use Yii;
use backend\models\Event;
use backend\models\EventParticipant;

/**
 * This is the model class for table "event_orders".
 *
 * @property int $id
 * @property int $event_id
 * @property int $type 1-coupon/2-gift
 * @property float $amount
 * @property int $selected_item
 * @property string $created_at
 * @property string $modified_at
 */
class EventOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'type', 'amount'], 'required'],
            [['event_id', 'type', 'selected_item'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'modified_at', 'selected_item'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'event_id' => Yii::t('app', 'Event ID'),
            'type' => Yii::t('app', 'Type'),
            'amount' => Yii::t('app', 'Amount'),
            'selected_item' => Yii::t('app', 'Selected Item'),
            'created_at' => Yii::t('app', 'Created At'),
            'modified_at' => Yii::t('app', 'Modified At'),
        ];
    }

    public function getPaymentStatus() {
      return $this->payment_status == 1 ? "Paid" : "Unpaid";
    }

    public function getVoucher() {
      return $this->hasOne(EventFoodVoucher::className(), ['id' => 'selected_item']);
    }

    public function getEvent() {
      return $this->hasOne(Event::className(), ['id' => 'event_id']);
    }

    public function getFoodVoucher() {
      return $this->hasOne(EventFoodVoucher::className(), ['id' => 'selected_item']);
    }

    public function getParticipants() {
      return $this->hasMany(EventParticipant::className(), ['order_id' => 'id']);
    }
}
