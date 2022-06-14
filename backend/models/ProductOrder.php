<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "event_gift_voucher".
 *
 * @property int $id
 * @property int|null $event_id
 * @property int|null $gift_voucher_id
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class ProductOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_product_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pk_order_id', 'pk_order_id', 'pk_order_id','pk_order_id','pk_order_id'], 'integer'],
        ];
    }

    public function search()
    {
        $query = ProductOrder::find()->where(['order_status'=>1]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['pk_order_id' => SORT_DESC]],
        ]);
        return $dataProvider;
    } 
    /**
     * {@inheritdoc}
     */
}
