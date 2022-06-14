<?php 
$amount=0;

?>
<table style="width:100%"  cellspacing="15"  >
<tbody>
  <tr>
<td>
  <table style="width:100%" >
    <tbody>
  <tr>
    <th style="text-align: left;">Your Order has been processed. </th>
    <th style="text-align: right;"><img src="https://prezenty.in/prezenty/backend/web/logo.png" alt="" height=80 width=80></img> </th>
  </tr>
  </tbody>
  </table>
</td>
</tr>

   <tr>
    <td><b>Hello <?php echo $mailData[0]['name'];?>,</b><br/>
    <?php  if($mailData[0]['status'] == 'COMPLETE') { ?>
    Your order has been successfully processed and will be dispatch it soon. We'll send tracking details over SMS and email. Your order details is giving below.
    <?php  } else { ?>  
    
    Your order has been failed. Please contact Prezenty customer support if you didn't receive your voucher in 1 hour. Sorry for the inconvenience caused.<br><br>
    Email: support@prezenty.in<br>
    Mobile: +91 8040509200
    <?php } ?>
    </td> 
    
  </tr>

  <tr>
    <td>
        <?php  if($mailData[0]['status'] == 'COMPLETE') { ?>
        
        <h4 style="margin-bottom: 5px;">Order Confirmed</h4>
        
        <?php } else { ?>

        <h4 style="margin-bottom: 5px;">Order Failed</h4>        
        
        <?php } ?>
    Order No: <?php echo $mailData[0]['invNo'];?></td>
  </tr>
  <tr>
      <td><b>Order Details</b><br/>
        Name: <?php echo $mailData[0]['name'];?><br/>
        Address: <?php echo $mailData[0]['address'];?><br/>
        Mobile No: <?php echo $mailData[0]['phone'];?><br/>
        Email: <?php echo $mailData[0]['email'];?><br/>
        Order ID: <?php echo $mailData[0]['invNo'];?><br/>
        Reference ID: <?php echo $mailData[0]['id'];?></td>
      </tr>
      <tr>
    <?php 
    $count=0;
    $amountR=$mailData[0]['amountR'];
    foreach ($mailData as $key => $value) {
        
        $amount+=$value['amount'];
    }   
    ?>          
    <td><b>Payment Details: </b><br/>
        Transaction ref: <?php echo $mailData[0]['id'];?><br/>
        Transaction Date: <?php echo date("d-m-Y"); ?><br/>
        Amount: <?php echo number_format((float) $amountR, 2, '.', ''); ?><br/>
        Status: <?php echo $mailData[0]['status'];?></td>
        </tr>
    <tr>
    <?php  if($mailData[0]['status'] == 'COMPLETE') { ?>
    <td><b>Delivery Details: </b><br/>
    
        Name: <?php echo $mailData[0]['rece_name'];?> <br/>
        Email:  <?php echo $mailData[0]['rece_email'];?><br/>
        Mob: <?php echo $mailData[0]['rece_mob'];?></td>
    <?php } ?>
    </tr>
<tr>
<tr><td><hr></td></tr>
<td>
  <table style="width:100%">
    <tbody>
      <tr><th style="text-align:left;">Items In Your Order</th></tr>
	  <tr>
		<td style="width:60%"></br><img src="<?php echo $mailData[0]['image']; ?>" alt="" height=90 width=140></img><br/></td>
		<td  style="padding: 10px; width:40% text-align:left;"><b> <?php echo $mailData[0]['voucher']; ?> </b>
		<br/> INR <?php echo number_format((float) $mailData[0]['amount'], 2, '.', ''); ?>
		<br/> Nos. <?php echo ($mailData[0]['count']); ?> 
	</td>
   </tr>
   <tr>
    <td><br/><b>Total Price: </b></td>
    <td><br/><b><?php echo number_format((float) ($amount*$mailData[0]['count']), 2, '.', ''); ?> </b></td>
    </tr>
    <tr>
    <td><b>Shipping Charges: </b></td>
    <td><b>0.00</b></td>
    </tr>
    <tr>
    <td><b>Processing Fee: </b></td>
    <td><b>0.00</b></td>
    </tr>
    <tr>
    <tr>
    <td><b>Service Charge: </b></td>
    <td><b><?php echo number_format((float) ($mailData[0]['serviceCharge']), 2, '.', ''); ?></b></td>
    </tr>
    <tr>        
    <td><b>Discount: </b></td>
    <td><b>0.00</b></td>
    </tr>
    <tr>
    <td><b>GST: </b></td>
    <td><b><?php echo floatval($mailData[0]['gst']); ?>% (<?php echo number_format((float) ($mailData[0]['serviceCharge']*($mailData[0]['gst']/100)), 2, '.', ''); ?>)</b></td>
    </tr>
    <tr>
    <td><b style="font-size: 18px; color: darkred;">Grand Total:</b></td>
    <td><b style="font-size: 18px; color: darkred;"><?php echo number_format((float) $amountR, 2, '.', ''); ?></b></td>
    </tr>
  </tbody>
  </table>
</td>
</tr>

<tr><td><hr></td></tr>
    <tr>
    <td style="text-align:center;">We are always happy to assist you.<br/>Thank you for shopping with us</b></td>
    </tr>
<tr><td><hr></td></tr>
      <tr>
      <td> 
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
      </td>
      </tr>
</tbody>
</table>
