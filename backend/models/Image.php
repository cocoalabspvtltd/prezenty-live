<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "music".
 *
 * @property int $id
 * @property string|null $music_file_url
 * @property int $status
 * @property string $created_at
 * @property string $modified_at
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_templates';
    }
    public $from_date,$to_date;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_file_url'], 'string'],
            [['status'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
            [['image_file_url'],'file', 'maxFiles' => 1,'extensions' => 'jpeg,png'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_file_url' => 'Image File',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }

    
    public function getImageFile(){
        $imagePath = $this->image_file_url;
        $locationPath = Yii::$app->params['upload_path_image_template_files'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }
}
