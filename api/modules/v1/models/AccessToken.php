<?php

namespace api\modules\v1\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use yii\db\Expression;
use Yii;

/**
 * This is the model class for table "tb_access_token".
 *
 * @property integer $id
 * @property integer $fk_access_token_user
 * @property string access_token
 * @property string $created_at
 * @property string $modified_at
 * @property string $expiry
 * @property integer $status
 */


class AccessToken extends \yii\db\ActiveRecord
{
  public static $db;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_token';
    }
		public function behaviors()
		{
			 return [
					 [

					 'class' => TimestampBehavior::className(),
					 'createdAtAttribute' => 'created_at',
					 'updatedAtAttribute' => 'modified_at',
					 'value' => new Expression('NOW()'),
							 // if you're using datetime instead of UNIX timestamp:
							 // 'value' => new Expression('NOW()'),
					 ],
			 ];
		}

     public static function getDb() {
        $db = Yii::$app->db;
        if(static::$db)
            $db = static::$db;
        return $db;
        //  $db = return Yii::$app->readDB;
    }
    public static function useReadDb(){ 
       static::$db = Yii::$app->readDB;       
    }
    public static function useWriteDb(){
       static::$db = Yii::$app->db;
    }
    public function getFkAccessTokenUser() {
      Account::useReadDb();
      return $this->hasOne(Account::className(),['id'=>'fk_access_token_user'])->andWhere(['status'=>1]);
    }
		public function generateForUser($modelUser) {

			$this->fk_access_token_user = $modelUser->id;
			$this->status = 1;
			$validityDays = isset(Yii::$app->params['accessTokenValidityDays'])?Yii::$app->params['accessTokenValidityDays']:30;
      $date = date('Y-m-d H:i:s', strtotime("+$validityDays day"));
      $this->ts_expiry = $date;

      $dt = new \DateTime($date);
      $dt->setTimezone(new \DateTimeZone('UTC'));
      $date = $dt->format('Y-m-d H:i:s');
      $date = strtotime($date);
      // print_r($date);exit;
      $now_seconds = time();
      $uid = (string)$modelUser['id'];

      $payload = [ 
                "exp" => $date,  // Maximum expiration time is one hour
                "uid" => $uid,
              ];
      // $data = Yii::$app->jwt->encode($payload);
      $this->access_token = sha1($modelUser->id.'_'.time());
			// $this->access_token = $data;
			$this->save(false);
		}
}
/* $connection = Yii::$app->db;
$connection->createCommand()->update('user', ['sha_password' => $password], 'id='.$id)->execute(); */
