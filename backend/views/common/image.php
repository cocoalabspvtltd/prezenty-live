<?php
  use yii\helpers\Url;
  use yii\helpers\html;
    
  if(isset($fieldName) && $fieldName){
      $imageField = $model->{$fieldName};
  }else{
      $imageField= $model->image;
   }
   if($imageField){ ?>
   <a href="<?php echo $model->getImage()?>" target="_blank">
   <img src="<?php echo $model->getImage()?>" alt="Image" style="height:100px;width:100px;">
   </a>
<?php
}
?>
