<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\MenuGift */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="menu-gift-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image_url')->fileInput() ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'rating')->textInput() ?>

    <?= $form->field($model, 'is_gift')->dropdownList(array('0'=>'No','1'=>'Yes'),['prompt'=>'select one...']) ?>

    <?= $form->field($model, 'is_veg')->dropdownList(array('0'=>'No','1'=>'Yes')) ?>

    <?= $form->field($model, 'is_non_veg')->dropdownList(array('1'=>'Yes','0'=>'No')) ?>

    <div class="form-group field-menugift-is_non_veg retailer">
        <label for="">Add Items</label>
        <?php if($model->getItems()){ ?>
            <?php foreach($model->getItems() as $item){ ?>
                <div class="row">
                    <div class="col-md-6 after-add-more">
                        <label for="">Title</label>
                        <input type="text" name="MenuGift[items][]" value="<?=$item->title?>" class="form-control border-0 m-wrap mr-2" placeholder="Type here">
                    </div>
                    <div class="col-md-3 after-add-more">
                        <label for="">Price</label>
                        <input type="text" name="MenuGift[itemPrice][]" value="<?=$item->price?>" class="form-control border-0 m-wrap mr-2" placeholder="Type here">
                    </div>
                    <button type="button" class="remove-link" style="margin-bottom: 10px;margin-top: 25px;">remove</button>
                </div>
                <br>
            <?php }?>
        <?php }?>
        <div class="row">
            <div class="col-md-6 after-add-more">
                <label for="">Title</label>
                <input type="text" name="MenuGift[items][]" class="form-control border-0 m-wrap mr-2" placeholder="Type here">
            </div>
            <div class="col-md-3 after-add-more">
                <label for="">Price</label>
                <input type="text" name="MenuGift[itemPrice][]" class="form-control border-0 m-wrap mr-2" placeholder="Type here">
            </div>
            <div class="col-md-3 after-add-more">
                <button type="button" class="btn btn-warning add-more" style="margin-bottom: 10px;margin-top: 25px;">Add Retailers </button>
            </div>
        </div>
        <br>
    </div>
                    

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs("
    $('.add-more').click(function(){
        $('.retailer').append(`
            <div class=\"row after-add-more\">
                <div class=\"col-md-6\">
                    <label for=\"\">Title</label>
                    <input type=\"text\" name=\"MenuGift[items][]\" class=\"form-control border-0 m-wrap mr-2\" placeholder=\"Type here\">
                </div>
                <div class=\"col-md-3\">
                    <label for=\"\">Price</label>
                    <input type=\"number\" name=\"MenuGift[itemPrice][]\" class=\"form-control border-0 m-wrap mr-2\" placeholder=\"Type here\">
                </div>
                <button type=\"button\" class=\"remove-link\" style=\"margin-bottom: 10px;margin-top: 25px;\">remove</button>
            </div>
            <br>
        `);
    });
    $('.retailer').on('click', '.remove-link', function() {
        console.log($(this).parent('div'));
        $(this).parent('div').remove();
    })
    $('#menugift-is_veg').parent('div').hide();
    $('#menugift-is_non_veg').parent('div').hide();
    $('#menugift-is_gift').change(function(){
        var gift = $('#menugift-is_gift option:selected').val();
        if(gift == 0){
            $('#menugift-is_veg').parent('div').show();
            $('#menugift-is_non_veg').parent('div').show();
        }else{
            $('#menugift-is_veg').parent('div').hide();
            $('#menugift-is_non_veg').parent('div').hide();
        }
    });
");