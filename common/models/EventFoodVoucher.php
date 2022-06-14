<?php

namespace common\models;

use Yii;
use common\models\FoodCouponBrandVoucher;

/**
 * This is the model class for table "event_food_vouchers".
 *
 * @property int $id
 * @property int $event_id
 * @property int $voucher_id
 */
class EventFoodVoucher extends \yii\db\ActiveRecord
{
    public $name;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_food_vouchers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'voucher_id'], 'required'],
            [['event_id', 'voucher_id'], 'integer'],
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
            'voucher_id' => Yii::t('app', 'Voucher ID'),
        ];
    }

    public function getVoucher() {
      return $this->hasOne(FoodCouponBrandVoucher::className(), ['id' => 'voucher_id']);
    }
}
