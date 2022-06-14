<!DOCTYPE html>
<?php 
$amount=0;
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>Tax invoice</title>
</head>

<body>
    <div class="row mt-5" style="padding: 10px; ">
        <div class="col-lg-12 col-12 col-xl-7 col-sm-11 mx-auto" style="border-style: groove; padding: 20px;">

			<table style="width: 100%;" >
				<tr>
					<td style="width: 30%;">
						<img
						src="logo.png"
						style="width: 65px;"
						/>
					</td>

					<td style="width: 70%;text-align: left;">
					  <p style="text-align: center;"><b>TAX INVOICE</b></p>
					</td> 
				</tr>
			</table>
			  
			 <br>

			<table style="width: 100%;" >
				<tr>
					<td style="width: 20%;">
					    <p>Billing To</p>,<br>
					   <?php echo $mailData[0]['name'];?>,<br>
					   <?php echo $mailData[0]['phone'];?><br>
					   <?php echo $mailData[0]['email'];?><br>
					   <?php echo $mailData[0]['address'];?><br>
					</td>
					<td style="width: 50%;"></td>
					<td style="width: 30%;">
						Invoice : #<?php echo $mailData[0]['invNo'];?><br>
						Date : <?php echo $mailData[0]['date'];?><br>
					</td>
			  </tr>
			</table>
			
            <br><p>Place of Supply:</p>    
			<br><p>Description of goods and services</p>
		
			<?php 
				$count=0;
				$amountR=$mailData[0]['amountR'];
				foreach ($mailData as $key => $value) {
					$amount+=$value['amount'];
				}   
			?>
			
			<table style="margin-top: 15px;border-top: 2px solid; width: 100%;" >
			  <tr>
				<th style="width: 21%;">Item</th>
				<th style="width: 42%;">No.of vouchers</th>
				<th style="width: 42%;">Unit Prize</th>
				<th style="width: 20%;">Total</th>
			  </tr>
			  <tr style="line-height:38px;">
				<td><?php echo $mailData[0]['voucher']; ?></td>
				<td><?php echo $mailData[0]['count']; ?></td>
				<td>₹<?php echo $mailData[0]['amount']; ?></td>
				<td>₹<?php echo ($mailData[0]['amount']*$mailData[0]['count']); ?></td>
			  </tr>
			  <tr style="line-height:30px; border-top: 1px solid;">
				<td></td>
				<td></td>
				<td>Sub Total</td>
				<td>₹<?php echo($mailData[0]['amount']*$mailData[0]['count']); ?></td>
			  </tr>
			</table>
<br>
			<table style="margin-top: 15px;border-top:  1px solid; width: 100%;">
			  <tr>
				<th style="width: 21%;">Item No</th>
				<th style="width: 42%;">Service Description</th>
				<th style="width: 42%;">Price</th>
				<th style="width: 20%;">Total</th>
			  </tr>
			  <tr style="line-height:38px;">
				<td>1.1</td>
				<td>Service Charge _prezenty</td>
				<td>₹<?php echo($mailData[0]['serviceCharge']); ?></td>
				<td>₹<?php echo($mailData[0]['serviceCharge']); ?></td>
			  </tr>
			  <tr style="line-height:38px;">
				<td>1.2</td>
				<td>Shipping/Delivery Charges_prezenty</td>
				<td>₹0</td>
				<td>₹0</td>
			  </tr>

			  <tr style="line-height:30px; border-top: 1px solid;">
				<td></td>
				<td></td>
				<td>GST <?php echo floatval($mailData[0]['gst']); ?>%</td>
				<td>₹<?php echo($mailData[0]['taxAmount']); ?></td>
			  </tr>
			  <tr style="line-height:30px;">
				<td></td>
				<td></td>
				<td>Sub Total</td>
				<td>₹<?php echo($mailData[0]['taxAmount'] + $mailData[0]['serviceCharge']); ?></td>
			  </tr>
			  <tr style="line-height:30px; border-top: 2px solid;">
				<td></td>
				<td></td>
				<td>Total</td>
				<td>₹<?php echo (($mailData[0]['amount']*$mailData[0]['count']) + ($mailData[0]['taxAmount'] + $mailData[0]['serviceCharge'])); ?></td>
			  </tr>

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
			GST NO- 29AAMCP2658N1ZK
				</td>
			  </tr>
			</table>
			<br>
			<table style="width: 100%;">
			  <tr>
				<td style="width:100%;"><p style="float: right;">Thank you for the order & business with Prezenty!</p></td>
			  </tr>
			  
			</table>
			<br>
			<br>
			<table style="width:100%">
                    <tbody>
                    <tr>
                        <td style="text-align:center;">
                            <p>* Terms & conditions apply</span>
                            </p>
                        </td>
                    </tr>
                    </tbody>
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

		</div>
	</div>
</body>
</html>
<style>
  th{
    font-size: 13px;
  }
</style>
