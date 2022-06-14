<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "chat".
 *
 * @property int $id
 * @property int|null $event_id
 * @property string|null $sender_email
 * @property string|null $receiver_email
 * @property string|null $date
 * @property string|null $time
 * @property string|null $message
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class Chat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'status'], 'integer'],
            [['date', 'time', 'created_at', 'modified_at'], 'safe'],
            [['message'], 'string'],
            [['sender_email', 'receiver_email'], 'string', 'max' => 255],
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
            'sender_email' => 'Sender Email',
            'receiver_email' => 'Receiver Email',
            'date' => 'Date',
            'time' => 'Time',
            'message' => 'Message',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
