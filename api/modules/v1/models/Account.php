<?php

namespace api\modules\v1\models;
use yii\helpers\Url;
use Yii;
use yii\data\ActiveDataProvider;
use backend\models\User;
use ReallySimpleJWT\Token;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $password_hash
 * @property int|null $fk_person
 * @property string|null $auth_key
 * @property string|null $password_reset_token
 * @property string|null $password_token_expiry
 * @property string|null $email
 * @property int|null $is_banned
 * @property int $status
 * @property string|null $created_at
 * @property string $modified_at
 * @property string|null $phone
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [ 
            [['status'], 'integer'],
            [['created_at', 'modified_at','name','email','phone_number','country_code'], 'safe'],
            [['username', 'password_hash', 'auth_key', 'role'], 'string', 'max' => 255],
            [['confirm_password','new_password'],'string','min'=>6],
            [['new_password'],'is_pass_required'],
            ['confirm_password', 'compare', 'compareAttribute'=>'new_password', 'message'=>"Passwords don't match" ],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }
    public function addUserApi($data){
        $modelUserApi = new UserApi;
        $modelUserApi->api_token = $data['api_token'];
        $modelUserApi->user_token = $data['user_token'];
        $modelUserApi->user_id = $data['user_id'];
        $modelUserApi->ts_expiry = date("Y-m-d H:i:s", strtotime("+1 hours"));
        $modelUserApi->date_added = date("Y-m-d H:i:s");
        $modelUserApi->save(false);
        return true;
    }
    function sendSMS($authKey,$senderId,$countryCode,$phone,$message) {
	    $curl = curl_init();
	    curl_setopt_array($curl, array(
	        CURLOPT_URL => "https://api.msg91.com/api/v2/sendsms?country=91",
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_ENCODING => "",
	        CURLOPT_MAXREDIRS => 10,
	        CURLOPT_TIMEOUT => 30,
	        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST => "POST",
	        CURLOPT_POSTFIELDS => "{ \"sender\": \"SOCKET\", \"route\": \"4\", \"country\": \"$countryCode\", \"sms\": [ { \"message\": \"$message\", \"to\": [ \"$phone\" ] } ] }",
	        CURLOPT_SSL_VERIFYHOST => 0,
	        CURLOPT_SSL_VERIFYPEER => 0,
	        CURLOPT_HTTPHEADER => array(
	            "authkey: $authKey",
	            "content-type: application/json"
	        ),
	    ));

	    $response = curl_exec($curl);
	    $err = curl_error($curl);

	    curl_close($curl);
	    if ($err) {
	        echo "cURL Error #:" . $err;
	    } else {
	        return $response;
	    }
	}
    public function getUserApi($otp,$api_token){
        $modelAccount = UserApi::find()
        ->where(['api_token'=>$api_token,'user_token'=>$otp,'status'=>1])
        ->one();
        if($modelAccount){
            $expiryDate = $modelAccount->ts_expiry;
            if($expiryDate > date("Y-m-d H:i:s")){
                return $modelAccount;
            }
        }
        return false;
    }
    public function getUserApiDetailsById($userId){
        $modelUser = User::findOne($userId);
        return $modelUser;
    }
    public function addApiSession($api_id, $session_id, $ip) {
        $modelUserApi = new ApiSession;
        $modelUserApi->user_id = $api_id;
        $modelUserApi->session_id = $session_id;
        $modelUserApi->ip = $ip;
        $modelUserApi->date_added = date("Y-m-d H:i:s");
        $modelUserApi->date_modified = date("Y-m-d H:i:s");
        $modelUserApi->save(false);
        return $modelUserApi->api_session_id;
    }
    public function setTokenInactive($id) {
        $modelUserApi = UserApi::findOne($id);
        $modelUserApi->status = 0;
        $modelUserApi->save(false);
        return true;
    }
    public function validateToken($token)
    {
        $secret = SECRET_KEY;
        $result = Token::validate($token, $secret);
        return $result;
    }
    public function getCusomerDetailsByAPI($token){
        $modelUser = User::find()
        ->leftJoin('api_session','api_session.user_id=user.id')
        ->where(['api_session.session_id'=>$token])
        ->andWhere(['user.status'=>1])
        ->select('id,name,email,country_code,phone_number,address,role,image_url,id_copy,is_social_sign_up')
        ->one();
        return $modelUser;
    }
    function getBearerToken($headers) {
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
