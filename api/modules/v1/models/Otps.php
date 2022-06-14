<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "otps".
 *
 * @property int $id
 * @property int $isUsed
 * @property string|null $user_token
 * @property string|null $api_token
 * @property int|null $user_account_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 * @property string|null $expiry
 */
class Otps extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'otps';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['isUsed', 'user_account_id', 'status'], 'integer'],
            [['created_at', 'modified_at', 'expiry'], 'safe'],
            [['user_token', 'api_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'isUsed' => 'Is Used',
            'user_token' => 'User Token',
            'api_token' => 'Api Token',
            'user_account_id' => 'User Account ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'expiry' => 'Expiry',
        ];
    }
}
