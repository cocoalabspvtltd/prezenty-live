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
class GiftVoucherTransactions extends \yii\db\ActiveRecord
{
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
            [['event_id', 'vendor_id', 'cleared', 'status'], 'integer'],
            [['amount'], 'number'],
            [['trn_date', 'created_at', 'modified_at'], 'safe'],
            [['amount'],'required']
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
            'vendor_id' => 'Vendor ID',
            'amount' => 'Amount',
            'trn_date' => 'Trn Date',
            'cleared' => 'Cleared',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
