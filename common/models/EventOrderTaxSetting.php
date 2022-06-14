<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "event_order_tax_settings".
 *
 * @property int $id
 * @property string $option_name
 * @property float $option_value
 * @property string $created_at
 * @property string $modified_at
 */
class EventOrderTaxSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_order_tax_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['option_name', 'option_value'], 'required'],
            [['option_value'], 'number'],
            [['created_at', 'modified_at'], 'safe'],
            [['option_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'option_name' => Yii::t('app', 'Option Name'),
            'option_value' => Yii::t('app', 'Option Value'),
            'created_at' => Yii::t('app', 'Created At'),
            'modified_at' => Yii::t('app', 'Modified At'),
        ];
    }
}
