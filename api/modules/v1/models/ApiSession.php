<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "api_session".
 *
 * @property int $api_session_id
 * @property int $user_id
 * @property string $session_id
 * @property string $ip
 * @property string $date_added
 * @property string $date_modified
 */
class ApiSession extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'session_id', 'ip', 'date_added', 'date_modified'], 'required'],
            [['user_id'], 'integer'],
            [['session_id'], 'string'],
            [['date_added', 'date_modified'], 'safe'],
            [['ip'], 'string', 'max' => 40],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'api_session_id' => 'Api Session ID',
            'user_id' => 'User ID',
            'session_id' => 'Session ID',
            'ip' => 'Ip',
            'date_added' => 'Date Added',
            'date_modified' => 'Date Modified',
        ];
    }
}
