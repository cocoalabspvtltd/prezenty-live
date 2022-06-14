<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "food_coupon_brand_vouchers".
 *
 * @property int $id
 * @property int $brand_id
 * @property int $coupon_value
 */
class FoodCouponBrandVoucher extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'food_coupon_brand_vouchers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['brand_id', 'coupon_value'], 'required'],
            [['brand_id', 'coupon_value'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'brand_id' => Yii::t('app', 'Brand ID'),
            'coupon_value' => Yii::t('app', 'Coupon Value'),
        ];
    }

    public function getBrand() {
      return $this->hasOne(FoodCouponBrand::className(), ['id' => 'brand_id']);
    }
}
