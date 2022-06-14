<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $event_id
 * @property float $amount
 * @property int|null $menu_gift_id
 * @property int $gift_count
 * @property int|null $menu_veg_id
 * @property int $veg_count
 * @property int|null $menu_non_veg_id
 * @property int $non_veg_count
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 * @property string|null $order_status
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'menu_gift_id', 'gift_count', 'menu_veg_id', 'veg_count', 'menu_non_veg_id', 'non_veg_count', 'status'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'modified_at'], 'safe'],
            [['order_status'], 'string', 'max' => 255],
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
            'amount' => 'Amount',
            'menu_gift_id' => 'Menu Gift ID',
            'gift_count' => 'Gift Count',
            'menu_veg_id' => 'Menu Veg ID',
            'veg_count' => 'Veg Count',
            'menu_non_veg_id' => 'Menu Non Veg ID',
            'non_veg_count' => 'Non Veg Count',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'order_status' => 'Order Status',
        ];
    }
}
