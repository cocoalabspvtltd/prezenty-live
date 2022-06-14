<table style="
            border-collapse: collapse;
            width: 100%;
            margin: 0 auto;
            padding: 25px;
            border: 1px solid #000000;
            ">
            <tbody>
                
                <tr style="background-color: #ffffff;line-height: 0;">
                    <td class="top_bar" style="padding: 20px 30px;">
                        <div style="text-align: center;font-size: 30px;font-weight: bold;color: #000669;">
                            
                            <img src="https://prezenty.in/favicon.png" style="width: 65px;"/>
                            
                        <span style="display: inline-block;width: 100%;line-height: normal;">Prezenty</span></div>
                    </td>
                </tr>
                <?php
                 foreach ($mailData as $key => $value) {
                    
                ?>                
                <tr>
                    <td style="padding: 0;margin: 0;line-height: 0;"><img src="<?= $value['image'] ?>" style="width: 100%;height: 280px;object-fit: cover;" alt=""></td>
                </tr>

                <tr class="temp_title" style="
                    background-color: #B72029;
                    padding: 20px 20px;
                    display: block;
                    display: block;
                    font-size: 16px;
                    ">
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Gift Card Number: <?= $value['card_number'] ?>
                        </h5>
                    </td>
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Card PIN: <?= $value['card_pin'] ?>
                        </h5>
                    </td>
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Validity: <?= $value['card_validity'] ?>
                        </h5>
                    </td>
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Activation Code: <?= $value['activationCode'] ?>
                        </h5>
                    </td>
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Activation Url: <?= $value['activationUrl'] ?>
                        </h5>
                    </td>                    
                </tr>
                <?php  } ?>
                <tr style="display: block;padding: 20px 20px;">
                    <td style="display: block;margin-bottom: 5px;font-size: 16px;">Hi <?= $mailData[0]['name'] ?> ,</td>
                    <td style="display: block;font-weight: bold;margin-bottom: 5px;">You've got a <?= $mailData[0]['gift_card'] ?> from <?= $mailData[0]['giftedBy'] ?> </td>
                    <td style="font-size: 25px;display: block;margin-bottom: 20px;"><?= $mailData[0]['currency_symbol'] ?><?= $mailData[0]['price'] ?></td>
                    <td style="display: block;margin-bottom: 15px;font-size: 14;line-height: 18px;">
                        <?= $mailData[0]['content'] ?> 
                    </td>
                </tr>
            </tbody>
        </table>