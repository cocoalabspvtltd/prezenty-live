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
class AccountStatement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        //return 'payment_order';
    }

    /**
     * {@inheritdoc}
     */

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_date' => 'From Date',
            'to_date' => 'To Date',
        ];
    }
}