<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\GiftVoucherTransaction;

/**
 * GiftVoucherTransactionSearch represents the model behind the search form of `backend\models\GiftVoucherTransaction`.
 */
class GiftVoucherTransactionSearch extends GiftVoucherTransaction
{
    public $from_date, $to_date, $showAll = false;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'event_id', 'vendor_id', 'cleared', 'status'], 'integer'],
            [['amount'], 'number'],
            [['trn_date', 'created_at', 'modified_at', 'from_date', 'to_date'], 'safe'],
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
        $query = GiftVoucherTransaction::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query, 
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->where([
          'vendor_id' => Yii::$app->session->get('vendor_id'),
          'status' => 1
        ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'event_id' => $this->event_id,
            'vendor_id' => $this->vendor_id,
            'amount' => $this->amount,
            'trn_date' => $this->trn_date,
            'cleared' => $this->cleared,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at,
        ]);

        if($this->from_date){
            $query->andFilterWhere(['>=','DATE(trn_date)',date('Y-m-d',strtotime($this->from_date))]);
        }
        if($this->to_date){
            $query->andFilterWhere(['<=','DATE(trn_date)',date('Y-m-d',strtotime($this->to_date))]);
        }

        if(!$this->from_date && !$this->to_date && $this->showAll == false){
            $query->andFilterWhere(['=','cleared', 0]);
        }

        return $dataProvider;
    }

    public function getTotal($params)
    {
        $query = GiftVoucherTransaction::find();
        // $query->select('user.name as name, user.id as user_id, event.id as event_id, SUM(gift_voucher_transactions.amount) as amount, (SELECT SUM(gift_voucher_redeem.amount) FROM gift_voucher_redeem WHERE gift_voucher_redeem.event_id=event.id AND gift_voucher_redeem.vendor_id=gift_voucher_transactions.vendor_id) as redeemed');
        $query->select('user.name as name, user.id as user_id, event.id as event_id, event.title as event_title, SUM(a.amount) as amount, c.totalRedeemed');
        $query->from(['a' => 'gift_voucher_transactions']);
        
        $queryRedeem  = new \yii\db\Query();
        $queryRedeem->select('SUM(redeemed.amount) as totalRedeemed, redeemed.event_id, vendor_id');
        $queryRedeem->from('gift_voucher_redeem as redeemed');
        // $queryRedeem->where('gift_voucher_redeem.event_id=event.id AND gift_voucher_redeem.vendor_id=gift_voucher_transactions.vendor_id)');

        $query->leftJoin('event', 'a.event_id = event.id');

        $query->leftJoin(['c' => $queryRedeem], 'c.vendor_id=a.vendor_id AND c.event_id=event.id');

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
          'a.vendor_id' => Yii::$app->session->get('vendor_id'),
          'a.status' => 1,
          'cleared' => 1
        ]);

        
        $query->leftJoin('user', 'event.user_id = user.id');
        //$query->leftJoin('gift_voucher_redeem', 'gift_voucher_redeem.user_id = user.id');

        $query->groupBy('a.event_id');

        return $dataProvider;
    }
}
