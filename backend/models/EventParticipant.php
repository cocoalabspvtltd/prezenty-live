<?php

namespace backend\models;

use Yii;
use common\models\EventOrder;

/**
 * This is the model class for table "event_participant".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property int|null $members_count
 * @property int $is_veg
 * @property int $need_food
 * @property int $need_gift
 */
class EventParticipant extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_participant';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'status', 'members_count', 'is_veg', 'need_food', 'need_gift','is_ordered','is_delivered'], 'integer'],
            [['created_at', 'modified_at', 'order_members_count'], 'safe'],
            [['address'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 50],
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
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'members_count' => 'Members Count',
            'is_veg' => 'Is Veg',
            'need_food' => 'Need Food',
            'need_gift' => 'Need Gift',
        ];
    }

    public function getAmount(){
        $modelMenuGift = MenuGift::find()
            ->leftJoin('event_menu_gift','event_menu_gift.menu_gift_id=menu_gift.id')
            ->where(['event_id'=>$this->event_id])->all();
        $need_gift = $this->need_gift;
        $members_count = $this->members_count;
        $amount = 0;
        if($need_gift == 1){
            foreach($modelMenuGift as $menuGift){
                if($menuGift->is_gift == 1){
                    $amount = $menuGift->price * $members_count;
                    return $amount;
                }
            }
        }
        $need_food = $this->need_food;
        $is_veg = $this->is_veg;
        if($need_food == 1 && $is_veg == 1){
            foreach($modelMenuGift as $menuGift){
                if($menuGift->is_veg == 1){
                    $amount = $menuGift->price * $members_count;
                    return $amount;
                }
            }
        }
        if($need_food == 1 && $is_veg == 0){
            foreach($modelMenuGift as $menuGift){
                if($menuGift->is_non_veg == 1){
                    $amount = $menuGift->price * $members_count;
                    return $amount;
                }
            }
        }
        return 0;
    }

    public function getDeliveryStatus() {
      return $this->delivery_status == 1 ? "Delivered" : "Undelivered";
    }

    public function getOrder() {
      return $this->hasOne(EventOrder::className(), ['id' => 'order_id']);
    }

    public function getEvent() {
      return $this->hasOne(Event::className(), ['id' => 'event_id']);
    }
}
