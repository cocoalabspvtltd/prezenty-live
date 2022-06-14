<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "menu_order_payment".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $participant_id
 * @property int $is_paid
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class MenuOrderPayment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_order_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'participant_id', 'is_paid', 'status'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
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
            'participant_id' => 'Participant ID',
            'is_paid' => 'Is Paid',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
