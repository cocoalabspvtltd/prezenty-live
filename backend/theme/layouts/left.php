<aside class="main-sidebar">

    <section class="sidebar">
        <?php // Admin Menu ?>
        <?php if(Yii::$app->user->identity->role == "super-admin") { ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'circle-o', 'url' => ['/site/index']],
                    ['label' => 'Users', 'icon' => 'circle-o', 'url' => ['/user/index']],
                    ['label' => 'Transaction Master', 'icon' => 'circle-o', 'url' => ['/report/transaction-master']],                    
                    [
                        'label' => 'Master',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Gift Vouchers', 'icon' => 'circle-o', 'url' => ['/gift-voucher/index']],
                            ['label' => 'Food Vouchers', 'icon' => 'circle-o', 'url' => ['/food-coupon/brands']],
                            ['label' => 'Music files', 'icon' => 'circle-o', 'url' => ['/music/index']],
                            ['label' => 'Order Details', 'icon' => 'circle-o', 'url' => ['/product/order']],
                            ['label' => 'Product Details', 'icon' => 'circle-o', 'url' => ['/product/index']],
                            ['label' => 'Contact Us', 'icon' => 'circle-o', 'url' => ['/user/contact-us']],
                            ['label' => 'Account Settlement', 'icon' => 'circle-o', 'url' => ['event/balance-settlement-view']],
                            ['label' => 'Money Transfer', 'icon' => 'circle-o', 'url' => ['event/money-transfer']],
                            ['label' => 'Tax Settings', 'icon' => 'circle-o', 'url' => ['event/tax-settings']],
                            ['label' => 'Failed Orders', 'icon' => 'circle-o', 'url' => ['product/failed-orders']],
                            ['label' => 'Razor Pay Failed', 'icon' => 'circle-o', 'url' => ['report/razorpay-failed']],
                            ['label' => 'Razor Pay Refund', 'icon' => 'circle-o', 'url' => ['report/refund-list']],
                            ['label' => 'Master Account Statement', 'icon' => 'circle-o', 'url' => ['report/account-statement']],  
                            //['label' => 'Failed Orders', 'icon' => 'circle-o', 'url' => ['product/re-request-init']],
                            //['label' => 'Menu Or Gifts', 'icon' => 'circle-o', 'url' => ['/menu-gift/index']],
                        ]
                    ],
                    ['label' => 'Events', 'icon' => 'circle-o', 'url' => ['/event/index']],
                    ['label' => 'Event Gift Vouchers', 'icon' => 'circle-o', 'url' => ['/event-transaction/index']],[
                        'label' => 'Reports',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Food Menu Report', 'icon' => 'circle-o', 'url' => ['/menu-report/index']],
                            ['label' => 'Fund Report', 'icon' => 'circle-o', 'url' => ['/menu-report/fund']],
                        ]
                    ],
                    //['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii']],
                ],
            ]
        ) ?>
        <?php } ?>
        <?php // Vendor Menu ?>
        <?php if(Yii::$app->user->identity->role == "voucher-admin") { ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'circle-o', 'url' => ['/vendor/dashboard']],
                    ['label' => 'Redeem', 'icon' => 'circle-o', 'url' => ['/vendor/redeem/create']],
                    ['label' => 'Redemptions', 'icon' => 'circle-o', 'url' => ['/vendor/redeem']],
                    ['label' => 'Users', 'icon' => 'circle-o', 'url' => ['/vendor/users']],
                    ['label' => 'Transactions', 'icon' => 'circle-o', 'url' => ['/vendor/transactions']],
                    [
                        'label' => 'Reports',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Transactions', 'icon' => 'circle-o', 'url' => ['/vendor/report/transactions']],
                            ['label' => 'Redemptions', 'icon' => 'circle-o', 'url' => ['/vendor/report/redemptions']],
                        ]
                    ],
                    //['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii']],
                ],
            ]
        ) ?>
        <?php } ?>

    </section>

</aside>
