<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "event_menu_gift".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $menu_gift_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class EventMenuGift extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_menu_gift';
    }
    public $title;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'menu_gift_id', 'status'], 'integer'],
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
            'menu_gift_id' => 'Menu Gift ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
