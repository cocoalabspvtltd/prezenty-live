<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "blocked_user".
 *
 * @property int $id
 * @property int|null $event_id
 * @property string|null $blocked_user_email
 * @property string|null $blocked_by_user_email
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class BlockedUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blocked_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'status'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
            [['blocked_user_email', 'blocked_by_user_email'], 'string', 'max' => 255],
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
            'blocked_user_email' => 'Blocked User Email',
            'blocked_by_user_email' => 'Blocked By User Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }

    public function getBlockedUser()
    {
      return $this->hasOne(User::className(), ['email' => 'blocked_user_email']);
    }

    public function getParticipant()
    {
      return $this->hasOne(EventParticipant::className(), ['email' => 'blocked_user_email']);
    }

    public function getBlockedByUser()
    {
      return $this->hasOne(User::className(), ['email' => 'blocked_by_user_email']);
    }
}
