<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\EventGiftVoucherReceived;

/**
 * EventGiftVoucherReceivedSearch represents the model behind the search form of `backend\models\EventGiftVoucherReceived`.
 */
class EventGiftVoucherReceivedSearch extends EventGiftVoucherReceived
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'event_gift_id', 'event_id', 'event_participant_id', 'status'], 'integer'],
            [['created_at', 'modified_at', 'transaction_id', 'barcode'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = EventGiftVoucherReceived::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // $query->leftJoin(['event_gift_voucher', 'event_gift_id', '=', 'event_gift_voucher.id']);
        // $query->where([
        //   'eventGiftVoucher.giftVoucher.user_id' => Yii::$app->user->identity->id
        // ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'event_gift_id' => $this->event_gift_id,
            'event_id' => $this->event_id,
            'event_participant_id' => $this->event_participant_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'transaction_id', $this->transaction_id])
            ->andFilterWhere(['like', 'barcode', $this->barcode]);

        return $dataProvider;
    }
}
