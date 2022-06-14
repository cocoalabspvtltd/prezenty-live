<?php

namespace backend\modules\events\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\EventParticipant;

/**
 * GiftVoucherTransactionSearch represents the model behind the search form of `backend\models\GiftVoucherTransaction`.
 */
class ParticipantSearch extends EventParticipant
{

    public $payment_status;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'delivery_status', 'payment_status'], 'integer'],
            [['delivery_status', 'payment_status', 'modified_at', 'from_date', 'to_date'], 'safe'],
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
        $query = EventParticipant::find()
          ->joinWith(['order']);

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
            'event_participant.event_id' => $this->event_id,
            'need_food' => 1,
            'delivery_status' => $this->delivery_status,
            'payment_status' => $this->payment_status
          ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        return $dataProvider;
    }
}
