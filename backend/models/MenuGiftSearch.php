<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\MenuGift;

/**
 * MenuGiftSearch represents the model behind the search form of `backend\models\MenuGift`.
 */
class MenuGiftSearch extends MenuGift
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'price', 'rating', 'is_gift', 'is_veg', 'is_non_veg', 'status'], 'integer'],
            [['title', 'created_at', 'modified_at','from_date','to_date'], 'safe'],
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
        $query = MenuGift::find()->where(['status'=>1]);

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
            'price' => $this->price,
            'rating' => $this->rating,
            'is_gift' => $this->is_gift,
            'is_veg' => $this->is_veg,
            'is_non_veg' => $this->is_non_veg,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        if($this->from_date){
            $query->andFilterWhere(['>=','DATE(created_at)',date('Y-m-d',strtotime($this->from_date))]);            
        }
        if($this->to_date){
            $query->andFilterWhere(['<=','DATE(created_at)',date('Y-m-d',strtotime($this->to_date))]);            
        }

        return $dataProvider;
    }
}
