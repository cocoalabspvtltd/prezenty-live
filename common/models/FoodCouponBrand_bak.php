<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "food_coupon_brands".
 *
 * @property int $id
 * @property string $name
 */
class FoodCouponBrand extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'food_coupon_brands';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 200],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    public function getVouchers()
    {
        return $this->hasMany(FoodCouponBrandVoucher::className(), ['brand_id' => 'id']);
    }
}
