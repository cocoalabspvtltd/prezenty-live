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
                        <div style="text-align: center;font-size: 30px;font-weight: bold;color: #000669;"><img src="<?= $logo ?>" alt="" style="width: 70px;">
                        <span style="display: inline-block;width: 100%;line-height: normal;">Prezenty</span></div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0;margin: 0;line-height: 0;"><img src="<?= $image ?>" style="width: 100%;height: 280px;object-fit: cover;" alt=""></td>
                </tr>
                <tr class="temp_title" style="
                    background-color: #B72029;
                    padding: 20px 20px;
                    display: block;
                    display: block;
                    font-size: 16px;
                    ">
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Gift Card Number: <?= $card_number ?>
                        </h5>
                    </td>
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Card PIN: <?= $card_pin ?>
                        </h5>
                    </td>
                    <td style="display: block;">
                        <h5 style="margin: 5px; color: #fff;">Validity: <?= $card_validity ?>
                        </h5>
                    </td>
                </tr>
                <tr style="display: block;padding: 20px 20px;">
                    <td style="display: block;margin-bottom: 5px;font-size: 16px;">Hi <?= $name ?> ,</td>
                    <td style="display: block;font-weight: bold;margin-bottom: 5px;">You've got a <?= $gift_card ?></td>
                    <td style="font-size: 25px;display: block;margin-bottom: 20px;"><?= $currency_symbol ?><?= $price ?></td>
                    <td style="display: block;margin-bottom: 15px;font-size: 14;line-height: 18px;">
                        <?= $content ?>
                    </td>
                </tr>
            </tbody>
        </table>