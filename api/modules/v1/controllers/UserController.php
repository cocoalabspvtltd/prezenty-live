<?php

namespace api\modules\v1\controllers;
use yii;
use yii\rest\ActiveController;
use backend\models\User;
use backend\models\Point;
use backend\models\ContactUs;
use api\modules\v1\models\Otps;
use api\modules\v1\models\AccessToken;
// use backend\models\Log;
use api\modules\v1\models\Account;
use api\modules\v1\models\LoginForm;
use yii\data\ActiveDataProvider;
use ReallySimpleJWT\Token;
use yii\web\UploadedFile;
use Razorpay\Api\Api;
use DateTime;
use DatePeriod;
use DateInterval;
use backend\models\BankAccount;
use linslin\yii2\curl;

/**
 * Country Controller API
 *
 * @author Budi Irawan <deerawan@gmail.com>
 */
class UserController extends ActiveController
{
    public $modelClass = 'backend\models\User';      
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        unset($actions['register']);
        return $actions;
    }  
    public function behaviors()
    {

        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);


        $behaviors['corsFilter'] = [

            'class' => \yii\filters\Cors::className(),

            'cors' => [

                'Origin' => ['*'],

                'Access-Control-Allow-Origin' => ['*'],

                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],

                'Access-Control-Request-Headers' => ['*'],

                'Access-Control-Allow-Credentials' => null,

                'Access-Control-Max-Age' => 86400,

                'Access-Control-Expose-Headers' => []

            ]

        ];

        return $behaviors;

    }    
    public function  actionRegister(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $name = isset($post['name'])?$post['name']:'';
        $email = isset($post['email'])?$post['email']:'';
        $phone_number = isset($post['phone_number'])?$post['phone_number']:'';
        $country_code = isset($post['country_code'])?$post['country_code']:'';
        $address = isset($post['address'])?$post['address']:'';
        $password = isset($post['password'])?$post['password']:'';

        $userDetails = null;
        $message = "";
        $token = "";
        $success = false;
        if($name && $email && $phone_number && $country_code && $address && $password){
            $modelUser = User::find()->where(['status'=>1])->andWhere(['or',['phone_number'=>$phone_number],['email'=>$email]])->one();
            if($modelUser){
                $message = "Phone number or Email already taken";
            }else{
                $model = new User;
                $model->name = $name;
                $model->email = $email;
                $model->phone_number = $phone_number;
                $model->country_code = $country_code;
                $model->address = $address;
                $model->role = 'admin';
                $model->username = $email;
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
                $model->save(false);

                $token = $this->createToken($model->id);
                (new Account)->addApiSession($model->id, $token, null);
                $userInfo = (new Account)->findOne($model->id);

                $success = true;
                $message = "Registered Successfully";
                $userDetails = array(
                    'id' => $userInfo->id,
                    'name' => $userInfo->name,
                    'email' => $userInfo->email,
                    'phone_number' => $userInfo->phone_number,
                    'role' => $userInfo->role,
                    'created_at' => $userInfo->created_at,
                    'modified_at' => $userInfo->modified_at,
                    'address' => $userInfo->address,
                    'country_code' => $userInfo->country_code,
                    'image_url' => $userInfo->image_url
                );

            }
        }else{
            $message = "Name, Email, Country Code, Phone Number, Address, Password cannot be blank";
        }
        $baseUrl = Yii::$app->params['base_path_profile_images'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'success' => $success,
            'message' => $message,
            'userDetails' => $userDetails,
            'apiToken' => $token,
        ];
        return $ret;
    }
    public function  actionSignUp(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $name = isset($post['name'])?$post['name']:'';
        $email = isset($post['email'])?$post['email']:'';
        $phone_number = isset($post['phone_number'])?$post['phone_number']:'';
        $country_code = isset($post['country_code'])?$post['country_code']:'';
        $address = isset($post['address'])?$post['address']:'';
        $password = isset($post['password'])?$post['password']:'';
        $salesPerson = isset($post['sales_person'])? $post['sales_person'] : '';

        $userDetails = null;
        $message = "";
        $token = "";
        $success = false;
        
        if($name && $email && $phone_number && $country_code /*&& $address*/ && $password){
            $modelUser = User::find()->where(['status'=>1])->andWhere(['or',['phone_number'=>$phone_number],['email'=>$email]])->one();
            if($modelUser){
                $message = "Phone number or Email already taken";
            }else{
                
                $model = new User;
                $model->name = $name;
                $model->email = $email;
                $model->phone_number = $phone_number;
                $model->country_code = $country_code;
                $model->address = $address;
                $model->role = 'admin';
                $model->username = $email;
                $model->sales_person = $salesPerson;
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
                $model->save(false);

                $token = $this->createToken($model->id);
                (new Account)->addApiSession($model->id, $token, null);
                $userInfo = (new Account)->findOne($model->id);

                $success = true;
                $message = "Registered Successfully";
                $userDetails = array(
                    'id' => $userInfo->id,
                    'name' => $userInfo->name,
                    'email' => $userInfo->email,
                    'phone_number' => $userInfo->phone_number,
                    'role' => $userInfo->role,
                    'created_at' => $userInfo->created_at,
                    'modified_at' => $userInfo->modified_at,
                    'address' => $userInfo->address,
                    'country_code' => $userInfo->country_code,
                    'image_url' => $userInfo->image_url,
                    'user_mpin'=>false
                );
            
            $phone=$phone_number;
            $name = str_replace(' ', '%20', $name);
            if($phone){
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=eSYE5Ad5xJcn1la83D13wLa4zY3Udb2J&type=1&to='.$phone.'&sender=PRZNTY&message=Welcome%20to%20Prezenty!%20Dear%20'.$name.'%20Thanks%20for%20creating%20an%20account%20with%20us.%20You%20can%20access%20it%20at%20any%20time.%20Thanks,%20Prezenty%20Team&template_id=1307164630594693052',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    ));
            
                    $response = curl_exec($curl);
                    curl_close($curl);
                }
                if($email){
                    $body="";
                    $appFromEmail="support@prezenty.in";
                    $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/welcome' ] ,$userDetails)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Welcome to Prezenty!')->setTextBody('Welcome to Prezenty!')->send();                
                }                     

            }
        }else{
            $message = "Name, Email, Country Code, Phone Number, Password cannot be blank";
        }
        $baseUrl = Yii::$app->params['base_path_profile_images'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'success' => $success,
            'message' => $message,
            'userDetails' => $userDetails,
            'apiToken' => $token,
        ];
        return $ret;
    }
    public function  actionSocial(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $name = isset($post['name'])?$post['name']:null;
        $email = isset($post['email'])?$post['email']:null;
        $phone_number = isset($post['phone_number'])?$post['phone_number']:null;
        $country_code = isset($post['country_code'])?$post['country_code']:null;
        $address = isset($post['address'])?$post['address']:null;

        $userDetails = [];
        $message = "";
        $token = "";
        $success = false;
        if($email){
            $modelUser = User::find()->where(['status'=>1])->andWhere(['email'=>$email])->one();
            if($modelUser){
                $token = $this->createToken($modelUser->id);
                (new Account)->addApiSession($modelUser->id, $token, null);
                $userInfo = (new Account)->findOne($modelUser->id);
                $userDetails = array(
                    'id' => $userInfo->id,
                    'name' => $userInfo->name,
                    'email' => $userInfo->email,
                    'phone_number' => $userInfo->phone_number,
                    'role' => $userInfo->role,
                    'created_at' => $userInfo->created_at,
                    'modified_at' => $userInfo->modified_at,
                    'address' => $userInfo->address,
                    'country_code' => $userInfo->country_code,
                    'image_url' => $userInfo->image_url
                );
                $success = true;
                $message = "Registered Successfully";
            }else{
                $model = new User;
                $model->name = $name;
                $model->email = $email;
                $model->phone_number = $phone_number;
                $model->country_code = $country_code;
                $model->address = $address;
                $model->role = 'admin';
                $model->username = $email;
                $model->is_social_sign_up = 1;
                $model->save(false);

                $token = $this->createToken($model->id);
                (new Account)->addApiSession($model->id, $token, null);
                $userInfo = (new Account)->findOne($model->id);

                $success = true;
                $message = "Registered Successfully";
                $userDetails = array(
                    'id' => $userInfo->id,
                    'name' => $userInfo->name,
                    'email' => $userInfo->email,
                    'phone_number' => $userInfo->phone_number,
                    'role' => $userInfo->role,
                    'created_at' => $userInfo->created_at,
                    'modified_at' => $userInfo->modified_at,
                    'address' => $userInfo->address,
                    'country_code' => $userInfo->country_code,
                    'image_url' => $userInfo->image_url
                );
                
                //$phone = $userInfo->phone_number;
                $phone=$phone_number;
                if($phone){
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=eSYE5Ad5xJcn1la83D13wLa4zY3Udb2J&type=1&to='.$phone.'&sender=PRZNTY&message=Welcome%20to%20Prezenty!%20Dear%20'.$name.'%20Thanks%20for%20creating%20an%20account%20with%20us.%20You%20can%20access%20it%20at%20any%20time.%20Thanks,%20Prezenty%20Team&template_id=1307164630594693052',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    ));
            
                    $response = curl_exec($curl);
                    curl_close($curl);
                }
            if($email){

                $body="";
                $appFromEmail="support@prezenty.in";
                $mail = Yii::$app->mailer->compose( [ 'html' => '@common/mail/welcome' ] ,$userDetails)->setFrom([$appFromEmail => 'Prezenty'])->setTo($email)->setSubject('Welcome to Prezenty!')->setTextBody('Welcome to Prezenty!')->send();                
                }                

            }
        }else{
            $message = "Email cannot be blank";
        }
        $baseUrl = Yii::$app->params['base_path_profile_images'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'success' => $success,
            'message' => $message,
            'userDetails' => $userDetails,
            'apiToken' => $token,
        ];
        return $ret;
    }
    
    public function createToken($userId){
        $secret = SECRET_KEY;
        $expiration = time() + 2629746; //time() + 3600; for one month
        $issuer = ISSUER;

        $token = Token::create($userId, $secret, $expiration, $issuer);

        return $token;
    }

    public function actionLogin(){
        header('Access-Control-Allow-Origin: *');
        $post = Yii::$app->request->post();
        $email = isset($post['email'])?$post['email']:'';
        $password = isset($post['password'])?$post['password']:'';
        $userDetails = [];
        $message = "";
        $token = "";
        $success = false;
        if($email && $password){

            $check = User::find()->where(['email' => $email,'status'=>1])->one();
            
            if(empty($check['password_hash'])){

                $message = "Password not set for the user.Please use social sign in or press forgot password to add new password";

            } else {

                $model = new LoginForm();
                $model->username = $email;
                $model->password = $password;
                $result = $model->login();

                if($result == 1){
                    $model = User::find()->where(['email' => $email,'status'=>1])->one();
                   // print_r($model); exit;
                    $token = $this->createToken($model->id);
                    (new Account)->addApiSession($model->id, $token, null);
                    $userInfo = (new Account)->findOne($model->id);
                    if($userInfo->user_mpin !=""){
                        $mpin = true;
                    }else{
                        $mpin = false;
                    }
                    $success = true;
                    $message = "Logined Successfully";
                    $userDetails = array(
                        'id' => $userInfo->id,
                        'name' => $userInfo->name,
                        'email' => $userInfo->email,
                        'phone_number' => $userInfo->phone_number,
                        'role' => $userInfo->role,
                        'created_at' => $userInfo->created_at,
                        'modified_at' => $userInfo->modified_at,
                        'address' => $userInfo->address,
                        'country_code' => $userInfo->country_code,
                        'image_url' => $userInfo->image_url,
                        'id_copy' => $userInfo->id_copy,
                        'user_mpin'=>$mpin
                    );
                }else{
                    $message = "Invalid Email or Password";
                }
            }

        }else{
            $message = "Email, Password cannot be blank";
        }
        $baseUrl = Yii::$app->params['base_path_profile_images'];
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'success' => $success,
            'message' => $message,
            'userDetails' => $userDetails,
            'apiToken' => $token,
        ];
        return $ret;
    }
    public function actionProfile(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $get = Yii::$app->request->get();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $headers = getallheaders();

        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $token = $this->getBearerToken($api_token);
        $validateToken = (new Account)->validateToken($token);
        if(!$validateToken){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $user_details = (new Account)->getCusomerDetailsByAPI($token); 
        if(!$user_details){
            Yii::$app->response->statusCode = 401;
            $msg = "Somthing went wrong.";
            $ret = [
                'message' => $msg,
                'statusCode' => 401,
                'success' => $success
            ];
            return $ret;
        }
        
        
        $bankAcc = BankAccount::find()->where(['user_id'=>$user_details->id,'status'=>1])->one();   
        $virtualAccount = '⚠️ Once created an event, your virtual account number will be visible here';//null;
        $accountBalance=0;
         if(!empty($bankAcc)){
            $virtualAccount = $bankAcc->va_number;
            
            $client_id=Yii::$app->params['decentroConfig']['client_id'];
            $client_secret=Yii::$app->params['decentroConfig']['client_secret'];
            $module_secret=Yii::$app->params['decentroConfig']['account_module'];
            $provider_secret=Yii::$app->params['decentroConfig']['provider_secret'];
            $baseUrlDecentro=Yii::$app->params['decentroConfig']['baseAPIURL'];
            $curl = curl_init();     
             $name=str_replace(' ', '', $user_details->name);
            $dataArray = array("account_number"=>$virtualAccount,"customer_id" =>$name.''.$user_details->id,"mobile_number" =>$user_details->phone_number,"ifsc"=>$bankAcc->va_ifsc);

                    $urlData = http_build_query($dataArray);

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $baseUrlDecentro.'core_banking/money_transfer/get_balance?'.$urlData,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',

                      CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "Accept: */*",
                        "client_id: $client_id",
                        "client_secret: $client_secret",
                        "module_secret: $module_secret",
                        "provider_secret: $provider_secret"
                    ),
                ));
                    $response = curl_exec($curl);
                    $response=json_decode($response);   
                   // print_r($response);exit;
                   
                   if(isset($response->presentBalance)){
                    
                        $accountBalance=$response->presentBalance;
                       
                   } 
         }
        
        $modelUser = User::find()->where(['status'=>1,'id'=>$user_details->id])->one();
        if($modelUser->user_mpin !=""){
            $mpin = true;
        }else{
            $mpin = false;
        }
        $user_details = [
            "virtualAccount" => $virtualAccount,
            "id" => $modelUser->id,
            "name" => $modelUser->name,
            "email" => $modelUser->email,
            "country_code" => $modelUser->country_code,
            "phone_number" => $modelUser->phone_number,
            "address" => $modelUser->address,
            "role" => $modelUser->role,
            "image_url" => $modelUser->image_url,
            "id_copy" => $modelUser->id_copy,
            "is_social_sign_up" => $modelUser->is_social_sign_up,
            "has_password" => $modelUser->password_hash != null,
            "user_mpin"=>$mpin,
            "vBalance" =>$accountBalance
            
            
        ];
        $baseUrl = Yii::$app->params['base_path_profile_images'];
        $msg = "Profile listed successfully";
        $success = true;
        $ret = [
            'baseUrl' => $baseUrl,
            'statusCode' => 200,
            'message' => $msg,
            'success' => $success,
            'userDetails' => $user_details
        ];
        return $ret;
    }
    function getBearerToken($headers) {
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    public function actionUpdateProfile(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $token = $this->getBearerToken($api_token);
        $validateToken = (new Account)->validateToken($token);
        if(!$validateToken){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $user_details = (new Account)->getCusomerDetailsByAPI($token); 
        if(!$user_details){
            Yii::$app->response->statusCode = 401;
            $msg = "Somthing went wrong.";
            $ret = [
                'message' => $msg,
                'statusCode' => 401,
                'success' => $success
            ];
            return $ret;
        }
        $name = isset($post['name'])?$post['name']:'';
        $email = isset($post['email'])?$post['email']:'';
        $phone_number = isset($post['phone_number'])?$post['phone_number']:'';
        $country_code = isset($post['country_code'])?$post['country_code']:'';
        $address = isset($post['address'])?$post['address']:'';
        if($name){
            $user_details->name = $name;
        }
        if($email){
            $user_details->email = $email;
            $model = User::find()->where(['!=','id',$user_details->id])->andWhere(['email'=>$email,'status'=>1])->one();
            if($model){
                $msg = "Email already taken";
                $success = false;
                $ret = [
                    'message' => $msg,
                    'statusCode' => 200,
                    'success' => $success
                ];
                return $ret;
            }
            $user_details->username = $email;
        }
        if($phone_number){
            $user_details->phone_number = $phone_number;
            $model = User::find()->where(['!=','id',$user_details->id])->andWhere(['phone_number'=>$phone_number])->one();
            if($model){
                $msg = "Phone number already taken";
                $success = false;
                $ret = [
                    'message' => $msg,
                    'statusCode' => 200,
                    'success' => $success
                ];
                return $ret;
            }
        }
        if($country_code){
            $user_details->country_code = $country_code;
        }
        if($address){
            $user_details->address = $address;
        }
        $images = UploadedFile::getInstanceByName('image');
        $idCopy = UploadedFile::getInstanceByName('id_copy');
        $filesLocation = Yii::$app->params['upload_path_profile_images'];
        $user = new User;
        $saveFile = $user->uploadAndSave($images, $filesLocation);
        $saveCopy = $user->uploadAndSave($idCopy, $filesLocation);
        $image = $user_details->image_url?$user_details->image_url:"";
        $id_copy = $user_details->id_copy?$user_details->id_copy:"";
        if(isset($saveFile)&&$saveFile!=null){
            $user_details->image_url = $saveFile;
        }
        else
        {
            $user_details->image_url = $image;
        }
        if(isset($saveCopy)&&$saveCopy!=null){
            $user_details->id_copy = $saveCopy;
        }
        else
        {
            $user_details->id_copy = $id_copy;
        }
        $user_details->save(false);
        $msg = 'Profile updated successfully';
        $success = true;
        $user_details =  (new Account)->getCusomerDetailsByAPI($token);
        $ret = [
            'statusCode' => $statusCode,
            'success' => $success,
            'message' => $msg,
            'userDetails' => $user_details
        ];
        return $ret;
    }
    public function actionUpdatePassword(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $post = Yii::$app->request->post();
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $token = $this->getBearerToken($api_token);
        $validateToken = (new Account)->validateToken($token);
        if(!$validateToken){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $user_details = (new Account)->getCusomerDetailsByAPI($token); 
        if(!$user_details){
            Yii::$app->response->statusCode = 401;
            $msg = "Somthing went wrong.";
            $ret = [
                'message' => $msg,
                'statusCode' => 401,
                'success' => $success
            ];
            return $ret;
        }
        $oldPassword = isset($post['old_password'])?$post['old_password']:'';
        $password = isset($post['password'])?$post['password']:'';
        if($oldPassword && $password){
            $email = $user_details->email;
            $model = new LoginForm();
            $model->username = $email;
            $model->password = $oldPassword;
            $result = $model->login();
            if($result == 1){
                $user_details->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
                $user_details->save(false);
                $success = true;
                $message = "Password updated successfully";
                $statusCode = 200;
                $ret = [
                    'message' => $message,
                    'success' => $success,
                    'statusCode' => $statusCode
                ];
                return $ret;
            }else{
                $message = "Invalid old password";
                $success = false;
                $statusCode = 200;
                $ret = [
                    'message' => $message,
                    'success' => $success,
                    'statusCode' => $statusCode
                ];
                return $ret;
            }
        }else{
            $msg = 'Old Password and Password cannot be blank';
            $success = false;
            $statusCode = 200;
            $ret = [
                'message' => $msg,
                'success' => $success,
                'statusCode' => $statusCode
            ];
            return $ret;
        }
    }
    public function actionForgotPassword(){
        header('Access-Control-Allow-Origin: *');
        $post = $_POST;
        $success = false;
        if(!$post){
            $post = Yii::$app->request->post();
        }
        $ret = [];
        if(!isset($post['email'])){
            Yii::$app->response->statusCode = 500;
            $code = 500;
            $message = "Email cannot be empty.";
        }else{
            $date = date('Y-m-d H:i:s');
            $model = User::find()->where(['status'=>1,'email'=>$post['email'],'role'=>'admin'])->one();
            if(!$model){
                Yii::$app->response->statusCode = 200;
                $code = 200;
                $message = "User with this email id is not found";
            }else{
                $email = $model->email;
                $otp = rand(1000,9999);
                $dateTime = date('Y-m-d H:i:s');
                $minutes_to_add = 5;
                $time = new DateTime($dateTime);
                $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
                $stamp = $time->format('Y-m-d H:i:s');
                $modelOtp = Otps::find()->where(['user_account_id' => $model->id,'status'=>1])->one();
                if(!$modelOtp){
                    $modelOtp = new Otps;
                }
                $modelOtp->user_token = $otp;
                $modelOtp->user_account_id = $model->id;
                $modelOtp->expiry = $stamp;
                $modelOtp->save(false);
                
                
                try{
                    $to = $email;
                // $to = ['email'=>$email];
                $token = $otp;
                $subject = 'Reset your password';
                $body = ' 
                    <html> 
                    <head> 
                        <title></title> 
                    </head> 
                    <body> 
                        <h1> Here is your otp. </h1>
                        <table cellspacing="0" style="border: 2px dashed #FB4314; width: 100%;"> 
                            <tr> 
                                <th>OTP :</th><td>'.$token.'</td> 
                            </tr> 
                        </table> 
                    </body> 
                    </html>';
                $headers = "MIME-Version: 1.0" . "\r\n"; 
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Prezenty <prezentyapp@gmail.com>";
                //mail($to, $subject, $body, $headers);
                //$from = ['email'=>'prezentyapp@gmail.com'];
                $message = $otp.' is the OTP for the verification process to Reset password. Do not share this with anyone.Thanks and Regards Prezenty Team'; 
                $appFromEmail='support@prezenty.in';
                $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($to)->setSubject('Forgot Password')->setTextBody($message)->send();
                
                //Yii::$app->email->sendMail($from,$to,$subject,$message);
                
                 //Yii::$app->email->sendOtp($modelPerson,$otp);
				
                } catch(\Exception $e){ }
                
				$phone = $model->phone_number;
				if($phone){
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=eSYE5Ad5xJcn1la83D13wLa4zY3Udb2J&type=1&to='.$phone.'&sender=PRZNTY&message='.$otp.'%20is%20the%20OTP%20to%20change%20your%20password%20for%20the%20Prezenty%20account.%20DO%20not%20share%20this%20OTP%20with%20anyone.&template_id=1307164690962501328',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'GET',
					));
			
					$response = curl_exec($curl);
					curl_close($curl);
				}
				
                $code = 200;
                Yii::$app->response->statusCode = 200;
                $message = "Otp sent mail successfully";
                $ret[]=[
                'otp'=>$otp
                ];
                $success = true;
            }
        }
        $ret = [
            'success' => $success,
            'result'=>$ret,
            'statusCode' => $code,
            'message' => $message
        ];
        return $ret;
    }
    public function actionVerifyOtp()
    {
        header('Access-Control-Allow-Origin: *');
        $ret = [];
        $post = $_POST;
        if(!$post){
            $post = Yii::$app->request->post();
        }
        $user_token = isset($post['otp'])?$post['otp']:''; 
        if(!$user_token){
            $code    = 500;
            Yii::$app->response->statusCode = 500;
            $message = "Otp is mandatory";
        }
        $current_timestamp = date('Y-m-d H:i:s'); 
        $modelUserToken = Otps::find()->where(['user_token'=>$user_token])->andWhere(['>','expiry',$current_timestamp])->andWhere(['status'=>1])->one();
        if(!$modelUserToken){
            // $code    = 401;
            // Yii::$app->response->statusCode = 401;
            // $message = "Otp not valid";
            $ret = [
                'result'     => null,
                'statusCode' => 401,
                'message'    => "Invalid otp"
            ];
            return $ret;
        }else{
            $modelAccount = User::find()->where(['status'=>1])->andWhere(['id'=>$modelUserToken->user_account_id])->one();
            if($modelAccount){
                $modelAccessToken =  new AccessToken;
                $modelAccessToken->generateForUser($modelAccount);
        
                $modelAccount->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
                $modelAccount->save(false);

                $code    = 200;
                Yii::$app->response->statusCode = 200;
                $message = "Otp verified successfully";
                $ret = [
                    //   'access_token' => $modelAccessToken->access_token,
                    'password_reset_token' => $modelAccount->password_reset_token,
                    'account_id'=> $modelAccount->id,
                    'otp_verified'=>1,
                ];
            }else{
                $code    = 401;
                Yii::$app->response->statusCode = 401;
                $message = "User not valid";
            }
        }
        $ret = [
            'result'     => $ret,
            'statusCode' => $code,
            'message'    => $message
        ];
        return $ret;
    }
    public function actionResetPassword()
    {
        header('Access-Control-Allow-Origin: *');
        $ret = [];
        $post  = $_POST;
        if(!$post){
            $post = Yii::$app->request->post();
        }
        if (!isset($post['account_id'])){
            $code    = 500;
            Yii::$app->response->statusCode = 500;
            $message = "Account id can not be empty";
        }elseif (!isset($post['new_password'])){
            $code    = 500;
            Yii::$app->response->statusCode = 500;
            $message = "New password can not be empty";
        }elseif (!isset($post['confirm_password'])){
            $code    = 500;
            Yii::$app->response->statusCode = 500;
            $message = "Confirm password can not be empty";
        }elseif (!isset($post['password_reset_token'])){
            $code    = 500;
            Yii::$app->response->statusCode = 500;
            $message = "Password reset token can not be empty";
        }else{
            // $modelUser = Account::findOne($post['account_id']);
            $modelUser = User::find()->where(['password_reset_token' => $post['password_reset_token'],'id'=>$post['account_id']])->andWhere(['status'=> 1])->one();
            if($modelUser && $post['new_password']==$post['confirm_password']){
                $modelUser->password_hash = Yii::$app->getSecurity()->generatePasswordHash($post['new_password']);
                $modelUser->save(false);
                Yii::$app->response->statusCode = 200;
                $code    = 200;
                $message = "Password reset successfully";
                $ret[] = [
                    'account_id'=>$modelUser->id
                ];
            }else{
                Yii::$app->response->statusCode = 401;
                $code    = 401;
                $message = "Incorrect details";
            }
        }
        $ret = [
            'result'     => $ret,
            'statusCode' => $code,
            'message'    => $message
        ];
        return $ret;
    }
    public function actionSendMail(){
         /*$testArray['mailData']='hAlign';
            $appFromEmail = 'albinworkz@gmail.com';
            $userInfo='albinkumpalamthanam@gmail.com';
            $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($userInfo)->setSubject('Welcome to Prezenty')->setTextBody('test')->send();*/
            
                    $curl = curl_init();
                    $phone="9544855856";
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=eSYE5Ad5xJcn1la83D13wLa4zY3Udb2J&type=1&to='.$phone.'&sender=PRZNTY&message=Welcome%20to%20Prezenty!%20Dear%20albin%20Thanks%20for%20creating%20an%20account%20with%20us.%20You%20can%20access%20it%20at%20any%20time.%20Thanks,%20Prezenty%20Team&template_id=1307164630594693052',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    ));
            
                    $response = curl_exec($curl);
                    curl_close($curl);            
        }
    
    
    public function actionContactUs()
        {
            $post = Yii::$app->request->post();
            $id=$post['user_id'];
            $appFromEmail= $post['email'];
            $phone= $post['phone'];
            $name= $post['name'];
            $message=$post['message'];
            
            //$appFromEmail = 'albinworkz@gmail.com';
            // if(!empty($id)){
            //     $modelUser = User::find()->where(['id'=>$id])->one();
            //     $appFromEmail = $modelUser->email;
            // } else {
            //     $appFromEmail = $post['email'];
            // }
                        
            
            // if(!empty($appFromEmail)){
            
            
                $userInfo='support@prezenty.in';
                $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($userInfo)->setSubject('Support Query')->setTextBody($message)->send();
                $model = new ContactUs();
                $model->message=$message;
                $model->email=$appFromEmail;
                $model->telephone=$phone; //isset($modelUser->phone_number)?$modelUser->phone_number:'';
                $model->name=$name;
                $model->user_id=$id;
                $model->save(false);
                
                $ret = [
                    'result'     => 'Success',
                    'status' => 200,
                    'success' =>True,
                    'message'    => 'Send Successfully'
                ];                


            // } else {
            //     $ret = [
            //         'result'     => 'Failed',
            //         'status' => 200,
            //         'success' =>False,
            //         'message'    => 'Mail Id is null'
            //     ]; 
            // }

            return $ret;           


        }  
    
    public function actionUserImage($user_id = null, $email = null)
    {
        
        if($user_id!=null){
         $modelUser = User::find()->where(['status'=>1, 'id'=>$user_id])->one();
        }elseif($email != null){
            $modelUser = User::find()->where(['status'=>1, 'email'=>$email])->one();
        }else{
            $fileName = "ic_person_avatar.png";
            $path = UPLOADS_PATH."/".$fileName;
            header("Content-Type: application/image");
            header("Content-Transfer-Encoding: Binary");
            // header("Content-Length:".filesize($path_to_zip));
            header("Content-Disposition: inline; filename=$fileName");
            readfile($path);
            exit;
        }
        if($modelUser == null){
            $fileName = "";
        }
        else{
             $fileName = $modelUser->image_url;
        }
       
        if($fileName==""){
            $dir = UPLOADS_PATH."/";
            $fileName = "ic_person_avatar.png";
        }else {
            $dir = Yii::$app->params['base_path_profile_images'];
        }
        
        $path = $dir.$fileName;
        header("Content-Type: application/image");
        header("Content-Transfer-Encoding: Binary");
        // header("Content-Length:".filesize($path_to_zip));
        header("Content-Disposition: inline; filename=$fileName");
        readfile($path);
        exit;
    }
        
        public function actionTestMail(){
            
         $testArray['mailData']='hAlign';
            $appFromEmail = 'support@prezenty.in';
            $userInfo='albinkumpalamthanam@gmail.com';
            $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($userInfo)->setSubject('Welcome to Prezenty')->setTextBody('test')->send();            
            
        }
        
        public function actionTestSms(){
            
                $phone="7994422426";
                $name='albin';
                $curl = curl_init();


                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://thesmsbuddy.com/api/v1/sms/send?key=eSYE5Ad5xJcn1la83D13wLa4zY3Udb2J&type=1&to='.$phone.'&sender=PRZNTY&message=Welcome%20to%20Prezenty!%20Dear%20'.$name.'%20Thanks%20for%20creating%20an%20account%20with%20us.%20You%20can%20access%20it%20at%20any%20time.%20Thanks,%20Prezenty%20Team&template_id=1307164630594693052',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    ));
            
                $response = curl_exec($curl);
                curl_close($curl);
        }
}
