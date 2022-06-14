<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\GiftVoucher;

/**
 * GiftVoucherSearch represents the model behind the search form of `backend\models\GiftVoucher`.
 */
class GiftVoucherSearch extends GiftVoucher
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['title', 'image_url', 'created_at', 'modified_at','from_date','to_date'], 'safe'],
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
        $query = GiftVoucher::find()->where(['status'=>1]);

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'image_url', $this->image_url]);

        if($this->from_date){
            $query->andFilterWhere(['>=','DATE(created_at)',date('Y-m-d',strtotime($this->from_date))]);            
        }
        if($this->to_date){
            $query->andFilterWhere(['<=','DATE(created_at)',date('Y-m-d',strtotime($this->to_date))]);            
        }


        return $dataProvider;
    }
}
