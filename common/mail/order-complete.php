<table style="width:100%; border:2px solid #000;"  cellspacing="15" bordercolor=#d8d8d8 >
<tbody>
  <tr>
<td>
  <table style="width:100%;">
    <tbody>
  <tr>
    <th style="text-align:left;">Hello <span><?= $mailData[0]['name'] ?>,</span><br/>
        You received a Gift From <?= $mailData[0]['giftedBy'] ?> !</th>
    <th><!--<img src="C:\Pics\H.gif" alt="" border=1 height=100 width=100></img>--><i class="fa fa-gift" aria-hidden="true"></i></th>
  </tr>
  </tbody>
  </table>
</td>
</tr>
<?php
 foreach ($mailData as $key => $value) {
    
?> 
  <tr>
    <td style="text-align: left;"><img src="<?php echo $value['image']; ?>" alt="" border=1 height=100 width=100></img></td>
  </tr>
  <tr>
      <td><p style="margin:0;"><b>Gift Card Number:<?= $value['card_number'] ?></b></p><br/>
        <p style="margin:0;"><b>Card PIN: <?= $value['card_pin'] ?></b></p><br/>
        <p style="margin:0;"><b>Validity:<?= $value['card_validity'] ?></b></p><br/>
        <p style="margin:0;"><b>Activation Code:<?= $value['activationCode'] ?></b></p><br/>
        <p style="margin:0;"><b>Activation Url:<?= $value['activationUrl'] ?></b></p></td>
      </tr>
    <?php } ?>
    <tr>
      <td><h4 style="margin-bottom: 5px;">Order No:</h4> <?= $mailData[0]['invNo'] ?></td>
    </tr>
    <tr>
      <td><h4 style="margin-bottom: 5px;">REF NO:</h4> <?= $mailData[0]['id'] ?></td>
    </tr>
    <tr>
    <td>Send the Gift Acknowledgement & Thank you note Now! </td>
    </tr>
    <tr>
    <td><button style="background-color: #4472C4; color: #fff; cursor: pointer; margin-right: 10px; padding: 10px 20px; border: none;">Received Thank You</button> <button style="background-color: #4472C4; color: #fff; cursor: pointer; margin-right: 10px; padding: 10px 20px; border: none;">Amazing!</button> <button style="background-color: #4472C4; color: #fff; cursor: pointer; margin-right: 10px; padding: 10px 20px; border: none;">Thank You for Remembrance</button></td>
    </tr>
    <tr>
      <td>
      <form style="width: 100%;">
      <label style="width:100%;" for="content">Write here:</label>
      <textarea style="width:100%;" for="content"> </textarea>
      <button style="background-color: #4472C4; color: #fff; cursor: pointer; margin-top: 20px; padding: 10px 20px; border: none;">Submit</button>
    </form>
    </td>
    </tr>
    <tr><td>We are always happy to assist you.</td></tr>
    <tr><td style="text-align:center;"><b>Gift Card Terms & Conditions | How to Redeem/Activate | Balance Check | Transaction History | Chat with Us24x7</b><br/>
            If you have any queries, please write to us at support@prezenty.in, Prezenty customer satisfaction team will serve you with utmost priority!</td></tr>
    <tr>
      <td><br />
      <table style="width:100%">
      <tbody>
      <tr>
      <td style="text-align:center;">
      <p><strong>CONNECT WITH US</strong></p>
      <p><a href="https://www.facebook.com/ourshopee"><span><img src="C:\Pics\H.gif" alt="" height=20 width=20></i></span></a><a href="https://plus.google.com/+OURSHOPEEcom/posts"><span><img src="C:\Pics\H.gif" alt="" height=20 width=20></span></a><a href="https://twitter.com/ourshopee"><span><img src="C:\Pics\H.gif" alt="" height=20 width=20></span></a></p>
      <p>Registered Address</p>
      <p>Prezenty Infotech Private Limited</p>
      <p>26 S R T Road, Shivajinagar, Bangalore, Karnataka, India-560062</p>
      <p>Email:&nbsp;<a href="mailto: info@prezenty.in"><span> info@prezenty.in</span></a><span> | T: +91.80.40509200 | www.prezenty.in&nbsp;</span>
        <p><span>Policy and Agreement |&nbsp;</span><span>Privacy Policy</span></p><span>&copy; 2022 prezenty.in. All rights reserved.</span></p>
      </td>
      </tr>
      </tbody>
      </table>
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