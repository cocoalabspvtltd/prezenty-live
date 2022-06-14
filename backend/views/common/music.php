<?php 
use yii\helpers\Url;
$url = $model->getMusicFile();
?>
<a onclick="window.open(this.href,'_blank');return false;" href="<?=$url?>"><?=$model->music_file_url?></a>