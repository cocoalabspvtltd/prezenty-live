<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "event_video_wishes_received".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $event_participant_id
 * @property string|null $video_url
 * @property string|null $caption
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class EventVideoWishesReceived extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_video_wishes_received';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'event_participant_id', 'status'], 'integer'],
            [['video_url', 'caption'], 'string'],
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
            'event_participant_id' => 'Event Participant ID',
            'video_url' => 'Video Url',
            'caption' => 'Caption',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
