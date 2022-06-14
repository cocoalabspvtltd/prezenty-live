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
class Music extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'music';
    }
    public $from_date,$to_date;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['music_file_url'], 'string'],
            [['status'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
            [['music_file_url'],'file', 'maxFiles' => 1,'extensions' => 'mp3'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'music_file_url' => 'Music File',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
        ];
    }

    
    public function getMusicFile(){
        $imagePath = $this->music_file_url;
        $locationPath = Yii::$app->params['base_path_music_files'];
        $path = $locationPath.$imagePath;
        return Url::to($path);
    }
}
