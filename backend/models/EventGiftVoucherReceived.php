<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "event_gift_voucher_received".
 *
 * @property int $id
 * @property int|null $event_gift_id
 * @property int|null $event_id
 * @property int|null $event_participant_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class EventGiftVoucherReceived extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_gift_voucher_received';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_gift_id', 'event_id', 'event_participant_id', 'status'], 'integer'],
            [['created_at', 'modified_at','amount','transaction_id','barcode'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_gift_id' => 'Event Gift ID',
            'event_id' => 'Event',
            'event_participant_id' => 'Participant Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
    public function getUser(){
        $model = EventParticipant::find()->where(['status'=>1,'id'=>$this->event_participant_id])->one();
        return ($model)?$model->name:'';
    }
    public function getEventModel(){
        $model = Event::find()->where(['status'=>1,'id'=>$this->event_id])->one();
        return ($model)?$model->title:'';
    }
    public function getEvent() {
        return $this->hasOne(Event::class, ['id' => 'event_id']);
    }

    public function getEventGiftVoucher() {
        return $this->hasOne(EventGiftVoucher::class, ['id' => 'event_gift_id']);
    }

    public function getParticipant() {
        return $this->hasOne(EventParticipant::class, ['id' => 'event_participant_id']);
    }

    public function getUserName($id){
        $modelEvent = Event::find()->where(['status'=>1,'id'=>$id])->one();
        $model = User::find()->where(['status'=>1,'id'=>$modelEvent->user_id])->one();
        return ($model)?$model->name:'';
    }

    public function getGiftVoucher($id){
        $model = GiftVoucher::find()->where(['id'=>$id])->one();
        return ($model)?$model->title:'';
    }
}
