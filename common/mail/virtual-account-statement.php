
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>Summary</title>
</head>

<body>
    <div class="row mt-5" style="padding: 10px; ">
        <div class="col-lg-12 col-12 col-xl-7 col-sm-11 mx-auto" style="    border-style: groove;
        padding: 20px;">

  <table style="width:100%" >
    <tbody>
  <tr>
    <th style="text-align: left;"><b>Account Summary</b></th>
    <th style="text-align: right;"><img src="https://prezenty.in/prezenty/backend/web/logo.png" alt="" height=80 width=80></img> </th>
  </tr>
  </tbody>
  </table>
<table>
  <tr>
    <p>Dear Sir,</p>
    <p>
    Following are the Prezenty Account Statement for the day <?php 
    $date = new \DateTime("now", new \DateTimeZone(Yii::$app->timeZone) );
    echo $date->format('d-m-Y'); ?> </p>

  </tr>
</table>
<table style="margin-top: 15px;border-top: solid;" border="1">
  <tr>
    <th style="width: 21%;">SI No</th>
    <th style="width: 42%;">Account</th>
    <th style="width: 42%;">Type</th>
    <th style="width: 20%;">Amount</th>
  </tr>
<?php  
    $i=1;
   
     
     
     
    foreach ($mailData as $key => $value) { 
    //print_r($mailData);exit;
     ?>
  <tr style="line-height:38px;border-top: solid;border-right: solid;">
    <td><?php echo $i; ?></td>
    <td><center><?php 
    
        if($value->type == 'CREDIT'){
            
            echo $value->senderAccountNumber;
            
        } else {
            
            echo $value->recieverAccountNumber;
        }
    
    
    ?></center></td>
    
    <td><?php echo $value->type; ?></td>
    
        <td><center><?php 
    
        if($value->type == 'CREDIT'){
            
            echo $value->depositAmount;
            
        } else {
            
            echo $value->withdrawalAmount;
        }
    
    
    ?></center></td>
  </tr>
<?php  $i++; } ?>
</table>
<br>
<br>
<table style="width: 100%;">
  <tr>
	<td style="width: 50%;float:'left">
	  Registered Address<br>
Prezenty Infotech Private Limited<br>
26 S R T Road, Shivajinagar, Bangalore<br>
Karnataka, India-560062<br>
GST NO: 29AAMCP2658N1ZK<br>
CIN: U72900KA2021PTC151471
	</td>
  </tr>
  <tr>
	<td style="width:100%;"><br><p style="text-align: right;">Thank you for the order & business with Prezenty!</p></td>
  </tr>
  <tr>
	<td style="width:100%;"><br><p style="text-align: left; font-size:12;">* Terms & conditions apply</p></td>
  </tr>
</table>
<hr>
<table style="width:100%">
        <tbody>
        <tr>
            <td style="text-align:center;">
                <p>Email: support@prezenty.in |&nbsp;</span><span>&copy; 2022 prezenty.in. All rights reserved.</span>
                </p>
            </td>
        </tr>
        </tbody>
    </table>
</div> </div>
</div>

</body>

</html>
<style>
  th{
    font-size: 13px;

  }

</style>