
<table style="width:100%"  cellspacing="15"  >
<tbody>
  <tr>
<td>
  <table style="width:100%" >
    <tbody>
  <tr>
    <th style="text-align: left;">Hello <?php echo $mailData[0]['name'];?>,</th>
    <th style="text-align: right;"><img src="https://prezenty.in/prezenty/backend/web/logo.png" alt="" height=80 width=80></img> </th>
  </tr>
  </tbody>
  </table>
</td>
</tr>

   <tr>
    <td> 
	 <?php  if($mailData[0]['status'] == 'UPDATE') { ?><p>Well done. You have successfully updated your event via Prezenty.</p>
	 <?php  } else { ?>
    <p>Well done. By signing up You've taken your first step towards a happier and smart life.You have successfully created an event via Prezenty. <br><br></p>
	<?php } ?>
    <p>Thanks, <br>
    Prezenty Team</p>
    </td> 
    
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
