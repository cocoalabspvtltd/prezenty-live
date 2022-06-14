<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\EventGiftVoucher;

/**
 * EventGiftVoucherSearch represents the model behind the search form of `backend\models\EventGiftVoucher`.
 */
class EventGiftVoucherSearch extends EventGiftVoucher
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'event_id', 'gift_voucher_id', 'status'], 'integer'],
            [['created_at', 'modified_at', 'barcode'], 'safe'],
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
    public function searchEvents($params)
    {
        $query = EventGiftVoucher::find()
        ->leftJoin('event','event.id=event_gift_voucher.event_id')
        ->leftJoin('gift_voucher','gift_voucher.id=event_gift_voucher.gift_voucher_id')
        ->where(['event_gift_voucher.status'=>1,'event.status'=>1,'gift_voucher.status'=>1])
        ->select('event_gift_voucher.*,event.title as eventTitle,gift_voucher.title as giftTitle,event.date as eventDate,gift_voucher.image_url as giftVoucherImage,event.user_id as user');

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
            'event_id' => $this->event_id,
            'gift_voucher_id' => $this->gift_voucher_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at
        ]);
        if($params){
            if($params['EventGiftVoucherSearch']['user']){
                $user = $params['EventGiftVoucherSearch']['user'];
                $query->andWhere(['event.user_id'=>$user]);
            }
            if($params['EventGiftVoucherSearch']['eventTitle']){
                $eventTitle = $params['EventGiftVoucherSearch']['eventTitle'];
                $query->andWhere(['event.id'=>$eventTitle]);
            }
            if($params['EventGiftVoucherSearch']['giftTitle']){
                $giftTitle = $params['EventGiftVoucherSearch']['giftTitle'];
                $query->andWhere(['gift_voucher.id'=>$giftTitle]);
            }
            if($params['EventGiftVoucherSearch']['eventDate']){
                $eventDate = date('Y-m-d',strtotime($params['EventGiftVoucherSearch']['eventDate']));
                $query->andWhere(['event.date'=>$eventDate]);
            }
        }

        $query->andFilterWhere(['like', 'barcode', $this->barcode]);

        return $dataProvider;
    }
}