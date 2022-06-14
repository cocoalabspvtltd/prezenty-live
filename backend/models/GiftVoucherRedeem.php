<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "gift_voucher_redeem".
 *
 * @property int $id
 * @property int|null $event_gift_voucher_id
 * @property float|null $amount
 * @property string|null $date
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class GiftVoucherRedeem extends \yii\db\ActiveRecord
{
    public $mobile, $total, $balance, $verify_method, $otp;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gift_voucher_redeem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_gift_voucher_id', 'status'], 'integer'],
            [['amount'], 'number'],
            [['date', 'mobile', 'verify_method'], 'safe'],
            [['mobile', 'amount', 'verify_method'], 'required', 'on' => 'create'],
            ['otp', 'validateOtp', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['otp', 'string', 'min' => 6, 'max' => 6],
            ['otp', 'required', 'when' => function($model) {
                return $model->verify_method != "" && $model->verify_method == 'otp';
            }, 'whenClient' => "function (attribute, value) {
                return $('#verify_method :radio:checked').val() == 'otp' }"],
            ['amount', 'integer', 'min' => 1],
            ['mobile', 'integer', 'min' => 1],
            //['mobile', 'string', 'min' => 10, 'max' => 10],
            ['amount', 'compare', 'compareAttribute' => 'balance', 'operator' => '<=', 'type' => 'number'],
        ];
    }

    public function validateOtp(
        $attribute,
        $params
    ) {
      $otp = \Yii::$app->session['otp'];

      if($this->verify_method == 'otp' && ($otp['mobile'] != $this->mobile || $otp['otp'] != $this->otp)) {
        $this->addError('otp', 'Invalid OTP');
      }
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'event_gift_voucher_id' => Yii::t('app', 'Gift Voucher ID'),
            'amount' => Yii::t('app', 'Amount'),
            'date' => Yii::t('app', 'Date'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'modified_at' => Yii::t('app', 'Modified At'),
            'otp' => Yii::t('app', 'OTP'),
            'mobile' => Yii::t('app', 'Barcode'),
        ];
    }

    public function getBalance($mobile) {
      $vendor_id = Yii::$app->session->get('vendor_id');
      $event_gift_voucher = EventGiftVoucher::findOne(['barcode' => $mobile, 'gift_voucher_id' => $vendor_id]);
    
      if( !$event_gift_voucher ) {
        return false;
      }

      $query = GiftVoucherTransaction::find();
      $query->select('SUM(amount) as total');
      $query->where([
        'vendor_id' => $vendor_id,
        'event_id' => $event_gift_voucher->event->id,
        'cleared' => 1
      ]);
      $received = $query->one();

      $redeemedQuery = static::find();
      $redeemedQuery->select('SUM(amount) as total');
      $redeemedQuery->where([
        'vendor_id' => Yii::$app->session->get('vendor_id'),
        'event_id' => $event_gift_voucher->event->id,
      ]);
      $redeemed = $redeemedQuery->one();

      $balance = $received->total - $redeemed->total;

      return [
        'total' => $received->total,
        'redeemed' => ($redeemed->total)? $redeemed->total : 0,
        'balance' => $balance,
        'user' => $event_gift_voucher->event->eventUser,
        'event_gift_voucher' => $event_gift_voucher
      ];
    }

    public function getVerificationMethods() {
      return [
        'id' => "ID",
        'otp' => "OTP"
      ];
    }

    public function getUser() {
      return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getEvent() {
      return $this->hasOne(Event::className(), ['id' => 'event_id']);
    }

    public function getEventGiftVoucher() {
      return $this->hasOne(EventGiftVoucher::className(), ['id' => 'event_gift_voucher_id']);
    }
}
