<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "event_order_invoices".
 *
 * @property int $id
 * @property int $order_id
 * @property float $amount
 * @property float $service
 * @property float $gst
 * @property float $cess
 * @property string $created_at
 * @property string $modified_at
 */
class EventOrderInvoice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_order_invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'amount', 'service', 'gst', 'cess'], 'required'],
            [['order_id'], 'integer'],
            [['amount', 'service', 'gst', 'cess'], 'number'],
            [['created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'amount' => Yii::t('app', 'Amount'),
            'service' => Yii::t('app', 'Service'),
            'gst' => Yii::t('app', 'Gst'),
            'cess' => Yii::t('app', 'Cess'),
            'created_at' => Yii::t('app', 'Created At'),
            'modified_at' => Yii::t('app', 'Modified At'),
        ];
    }

    public function getOrder()
    {
      return $this->hasOne(EventOrder::className(), ['id' => 'order_id']);
    }
}