<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "gift_voucher_transactions".
 *
 * @property int $id
 * @property int $event_id
 * @property int $vendor_id
 * @property float $amount
 * @property string $trn_date
 * @property int $cleared
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class GiftVoucherTransaction extends \yii\db\ActiveRecord
{
    public $name, $total, $user_id, $event_title, $totalRedeemed;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gift_voucher_transactions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'vendor_id', 'amount', 'trn_date'], 'required'],
            [['event_id', 'vendor_id', 'cleared', 'status'], 'integer'],
            [['amount'], 'number'],
            [['trn_date', 'created_at', 'modified_at'], 'safe'],
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
            'vendor_id' => Yii::t('app', 'Vendor ID'),
            'amount' => Yii::t('app', 'Amount'),
            'trn_date' => Yii::t('app', 'Date'),
            'cleared' => Yii::t('app', 'Cleared'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'modified_at' => Yii::t('app', 'Modified At'),
            'isCleared' => Yii::t('app', 'Status'),
            'event_title' => Yii::t('app', 'Event'),
        ];
    }

    public function getIsCleared() {
      return ($this->cleared == 1 ? "Make Not Clear" : "Make Clear");
    }

    public function getRedeemed() {
      return $this->totalRedeemed ?? '0';
    }

    public function getEvent() {
      return $this->hasOne(Event::className(), ['id' => 'event_id']);
    }

    public function getVendor() {
      return $this->hasOne(GiftVoucher::className(), ['id' => 'vendor_id']);
    }
}
