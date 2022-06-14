<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "menu_gift_items".
 *
 * @property int $id
 * @property int|null $menu_gift_id
 * @property string|null $title
 * @property int $price
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class MenuGiftItems extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_gift_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['menu_gift_id', 'price', 'status'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'menu_gift_id' => 'Menu Gift ID',
            'title' => 'Title',
            'price' => 'Price',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
}
