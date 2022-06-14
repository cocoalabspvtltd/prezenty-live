<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $participant_id
 * @property int|null $type
 * @property int|null $type_id
 * @property string|null $message
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'participant_id', 'type_id', 'status'], 'integer'],
            [['message'], 'string'],
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
            'type' => 'Type',
            'type_id' => 'Type ID',
            'message' => 'Message',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
