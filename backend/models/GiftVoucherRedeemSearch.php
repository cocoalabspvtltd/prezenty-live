<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\GiftVoucherRedeem;

/**
 * GiftVoucherRedeemSearch represents the model behind the search form of `backend\models\GiftVoucherRedeem`.
 */
class GiftVoucherRedeemSearch extends GiftVoucherRedeem
{
    public $from_date, $to_date;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'event_gift_voucher_id', 'user_id', 'status'], 'integer'],
            [['amount'], 'number'],
            [['date', 'created_at', 'modified_at', 'from_date', 'to_date'], 'safe'],
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
        $query = GiftVoucherRedeem::find();

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

        $query->where([
            'vendor_id' => Yii::$app->session->get('vendor_id')
        ]);

        if($this->from_date){
            $query->andFilterWhere(['>=','DATE(`date`)',date('Y-m-d',strtotime($this->from_date))]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=','DATE(`date`)',date('Y-m-d',strtotime($this->to_date))]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'event_gift_voucher_id' => $this->event_gift_voucher_id,
            'user_id' => $this->user_id,
            'event_id' => $this->event_id,
            'amount' => $this->amount,
            'date' => $this->date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at,
        ]);

        return $dataProvider;
    }
}
