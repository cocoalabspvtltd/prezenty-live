<?php

namespace backend\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $password_hash
 * @property string|null $auth_key
 * @property string|null $role
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 * @property string|null $address
 * @property string|null $image_url
 * @property float|null $country_code
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'modified_at',
                'value' => new Expression('NOW()'),
            ]
        ];
    }
    public $from_date,$to_date,$new_password,$confirm_password;
    /**
     * {@inheritdoc}
     */

    public function scenarios() {
        return  [
                    self::SCENARIO_DEFAULT => [
                        'id_copy','name','email','phone_number','address','new_password','confirm_password','status','created_at','modified_at','password_hash','role','country_code','is_social_sign_up'
                    ],
                    'create' => [
                        'name','email','phone_number','address','new_password','confirm_password','status','created_at','modified_at','password_hash','role','country_code'
                    ],
                    'update' => [
                        'name','email','phone_number','address','new_password','confirm_password','status','created_at','modified_at','password_hash','role','country_code'
                    ],
                    'profile' => [
                        'name','email','username','image_url','new_password','confirm_password'
                    ],
                    'voucher-admin' => [
                        'name','email','username','new_password','confirm_password','role'
                    ],
                    'voucher-admin-update' => [
                        'name','email','username','new_password','confirm_password'
                    ]
                ];
    }

    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['created_at', 'modified_at','id_copy','is_social_sign_up', 'sales_person'], 'safe'],
            [['address'], 'string'],
            [['country_code'], 'number'],
            [['country_code'], 'string','min'=>2,'max'=>4],
            [['username', 'name', 'email', 'password_hash', 'auth_key', 'role', 'image_url'], 'string', 'max' => 255],
            [['image_url'],'file', 'maxFiles' => 1,'extensions' => 'png, jpg, jpeg'],
            [['email','name','phone_number','address','country_code'],'required'],
            [['email'],'unique'],
            [['phone_number'],'string','min'=>8,'max'=>15],
            [['phone_number'],'number'],
            [['new_password','confirm_password'],'required','on'=>'create'],
            [['new_password','confirm_password'],'required','on'=>'voucher-admin'],
            [['confirm_password','new_password'],'string','min'=>6,'max'=>'20'],
            [['new_password'],'is_pass_required'],
            ['confirm_password', 'compare', 'compareAttribute'=>'new_password', 'message'=>"Passwords don't match" ],
        ];
    }

    public function is_pass_required($attribute){
        $password = $this->new_password;
        $newPassword = $this->confirm_password;
        if($password && $newPassword == ''){
            $this->addError('confirm_password','Re-type Password cannot be blank.');
            return false;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'name' => 'Name',
            'email' => 'Email',
            'phone_number' => 'Phone Number',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'address' => 'Address',
            'image_url' => 'Image',
            'country_code' => 'Country Code',
            'new_password' => 'Password',
            'confirm_password' => 'Re-type Password'
        ];
    }

    
    public function uploadAndSave($images,$params=null)
    {
       $retId = false;
       if(!$images)
  	     $images = UploadedFile::getInstances($this,'uploaded_files');
       if(!is_array($images))
       {
         $images = [$images];
         $retId = true;
       }
       $ret= [];
       $uploads_path = Yii::$app->params['uploads_path'];
       if($params){
         $uploads_path = $params;
       }
       foreach($images as $image) {
            $newImage = User::renameImage($image);
            $image_path = $uploads_path.$newImage;
            $image_full_path = Yii::getAlias($image_path);
            $image->saveAs($image_full_path);
            $ret = $newImage;
       }
       return $ret;
    }
    public static function renameImage($image)
    {
        $name_tmp = isset($image->name)?$image->name:$image['name'];
        $name_tmp = explode('.',$name_tmp );
        $ext = $name_tmp[sizeof($name_tmp)-1];
        array_pop($name_tmp);
        $unique_num  = sha1(time());
        $name_tmp[] = $unique_num;
        $name_tmp = implode('-',$name_tmp).'.'.$ext;
        $image = $name_tmp;
        return $image;
    }

    public function getImage(){
        $imagePath = $this->image_url;
        $locationPath = Yii::$app->params['base_path_profile_images'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }
    
    public function getIdCopy(){
        $imagePath = $this->id_copy;
        $locationPath = Yii::$app->params['base_path_profile_images'];
        $path = $locationPath . $imagePath;
        return Url::to($path);
    }
}
