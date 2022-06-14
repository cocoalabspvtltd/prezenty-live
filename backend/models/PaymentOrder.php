<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "payment_order".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $gift_id
 * @property int|null $participant_id
 * @property string|null $order_id
 * @property float|null $amount
 * @property float|null $converted_amount
 * @property string|null $currency
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class PaymentOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'gift_id', 'participant_id', 'status'], 'integer'],
            [['amount', 'converted_amount'], 'number'],
            [['created_at', 'modified_at'], 'safe'],
            [['order_id'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 20],
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
            'gift_id' => 'Gift ID',
            'participant_id' => 'Participant ID',
            'order_id' => 'Order ID',
            'amount' => 'Amount',
            'converted_amount' => 'Converted Amount',
            'currency' => 'Currency',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
