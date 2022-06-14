<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
          crossorigin="anonymous">
    <script src="https://use.fontawesome.com/8736e0aa09.js"></script>

    <style>
  th{
    font-size: 13px;
  }

    </style>
</head>
<body>

<div class="row">
    <div class="col-lg-12 col-12 col-xl-12 col-sm-12 mx-auto"
         style="padding: 20px;">
		 
        <div class="row">
            <div class="container-fluid" style="padding: 20px;">
            <div  style="float: right; ">
			<div >
			<img src="https://prezenty.in/prezenty/backend/web/logo.png"
								 alt="" height=75
								 width=80/>
		  </div>
		  </div>
		  <div   style="float: left;
		  width: 80%; ">
			<h3>Hello <?= $mailData[0]['name'] ?>,</h3>
			<h3>You got a gift from <?= $mailData[0]['giftedBy'] ?>!</h3>
		  </div>
		</div>
        </div>
        
        <br>
        <br>
        <br>
        <div style="padding: 10px;">
            <div class="row">
                <img src="<?php echo $mailData[0]['voucherImg']; ?>" alt="" style="width: 400px; height: 200px; display: block;
  margin-left: auto;
  margin-right: auto; ">

                <div style="padding: 10px;"><p style="text-align: center; font-size:18px">
                    <b><?php echo $mailData[0]['gift_card']; ?></b></p></div>
            </div>
			<?php foreach ($mailData as $key => $value) { ?>  
            <div class="row card" style=" border-style: groove; padding: 20px;">
                <div class="col-12 col-lg-12 col-md-12">
                    <span>Card No: <b><?= $value['card_number'] ?></b></span>
                </div>
                <div class="col-8 col-lg-8 col-md-8">
                    <span>Card Pin: <b><?= $value['card_pin'] ?></b></span>
                </div>
                <br>
                <div class="col-4 col-lg-4 col-md-4">
                    <span><b>₹<?= $value['amount'] ?></b></span>
                </div>

                <div class="col-12 col-lg-12 col-md-12">
                    <span>Validity: <b><?= $value['card_validity'] ?></b></span><br>
                </div>
				
				<?php if($value['activationUrl']!='') { ?>  
					<br>
					<div class="col-8 col-lg-8 col-md-8">
						<span>Activation Code: <b><?= $value['activationCode'] ?></b></span><br>
					</div>
					<div class="col-12 col-lg-12 col-md-12">
						<span>Activation Url: <a href="<?= $value['activationUrl'] ?>"><b><?= $value['activationUrl'] ?></b></a></span><br>
					</div>
				<?php } ?>
				
				<table style="width: 100%;">
				<tr>
				<td style="width: 50%; align: center;">
				<table style="margin-left: auto; margin-right: auto;">
                		        <tr>
                    		        <td style="padding: 10px;">
                    		            <a href="https://prezenty.in/balance/<?= $value['card_number'] ?>/<?= $value['card_pin'] ?>" >
                    		                <button type="button " class="mt-2 mt-sm-0 check-btn btn-primary"> Check Card Balance </button> </a>
                    		        </td>
                    		        <td style="padding: 10px;">
                    		            <a href="https://prezenty.in/transaction-history/<?= $value['card_number'] ?>/<?= $value['card_pin'] ?>">
                    		                <button type="button " class="mt-2 mt-sm-0 check-btn btn-primary"> Transaction History </button></a>
                    		        </td>
                		        </tr>
            		        </table></td>
				</tr>
				</table>
			
            </div>
            <br>
			<?php } ?>
			
            <div class="row">
                <div class="col-lg-12 col-12 col-md-12">
                    <span><i class="fa fa-gift" aria-hidden="true"></i> Gifted by,</span>
                </div>
                <div class="row">
                    <div class="col-lg-1 col-3 col-md-3"></div>
                    <div class="col-lg-9 col-9 col-md-9">
                        <span><?= $mailData[0]['giftedBy'] ?></span><br>
                        <span><?= $mailData[0]['email'] ?></span><br>
                        <span><?= $mailData[0]['mob'] ?></span><br>
                    </div>
                </div>


            </div>

            <br>
            <br>
            
            <div class="col-12 col-lg-12 col-md-12">
                <span><b>Terms & conditions</b></span><br>
            </div>
            <div class="col-12 col-lg-12 col-md-12">
                <span><?= $mailData[0]['content'] ?></span><br>
            </div>
            
            <hr>
            
            <footer class="mt-3">
                <table style="width:100%">
                    <tbody>
                    <tr>
                        <td style="text-align:center;">
                            <p><strong>CONNECT WITH US</strong></p>
                            <p>
                                <a href="https://www.youtube.com/channel/UC6jw213kMEi1t-HI6lORtVQ"><span><img
                                        src="https://prezenty.in/prezenty/backend/web/ic_yt.png"
                                        alt="" height=26 width=26></span></a>&nbsp;|&nbsp;<a href="https://instagram.com/prezentyapp"><span><img
                                    src="https://prezenty.in/prezenty/backend/web/ic_insta.png"
                                    alt="" height=26 width=26></span></a></p>
                            <p>Registered Address</p>
                            <p>Prezenty Infotech Private Limited<br>26 S R T Road, Shivajinagar,
                                Bangalore, Karnataka, India-560062</p>
                            <p>Email:&nbsp;<a href="mailto:support@prezenty.in"><span>support@prezenty.in</span></a><span> |&nbsp;Policy and Agreement |&nbsp;</span><span>Privacy Policy</span><span><br/></span><span>&copy; 2022 prezenty.in. All rights reserved.</span>
                            </p>
                        </td>
                    </tr>
					
					<tr>
        		        <td> 
            				<table style="margin-left: auto; margin-right: auto;">
                		        <tr>
                    		        <td style="padding: 10px;">
                    		            <a href="https://apps.apple.com/in/app/prezenty/id1589909513" >
                    		                <img src="https://cdnstatic.yougotagift.com/static/img/mobile_apps/app-store.png" style="width: 110px; vertical-align: middle;"></a>
                    		        </td>
                    		        <td style="padding: 10px;">
                    		            <a href="https://play.google.com/store/apps/details?id=com.cocoalabs.event_app">
                    		                <img src="https://cdnstatic.yougotagift.com/static/img/mobile_apps/google-play.png" style="width: 110px; vertical-align: middle;"></a>
                    		        </td>
                		        </tr>
            		        </table>
        		        </td>
    		        </tr>
    		        
                    </tbody>
                </table>
				
            </footer>

        </div>

    </div>
</div>

</body>
</html>
