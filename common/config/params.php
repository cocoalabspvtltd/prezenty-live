<?php 
// define('UPLOADS_PATH','https://cocoalabs.in/event/common/uploads');

define('SECRET_KEY','sec!ReT413*&');
define('ISSUER','localhost');


//live
define('KEY_ID','rzp_live_kebvQVzqxgwLB7');
define('KEY_SECRET','T8ssUMeKc4FZfM3mCGXNWJ4M');


// test
/*  define('KEY_ID','rzp_test_ehOEYz6VwRTqHe');
  define('KEY_SECRET','avToeQ6kjsnCfqdeE4m5zbri');*/


//define('UPLOADS_PATH','http://prezenty.in/event-app/common/uploads');
define('UPLOADS_PATH','https://prezenty.in/prezenty/common/uploads');
// define('WOOHOO_URL','https://sandbox.woohoo.in');

// define('SECRET_KEY','sec!ReT413*&');
// define('ISSUER','localhost');
// define('KEY_ID','rzp_test_gnORUZc76xrfhJ');
// define('KEY_SECRET','f8Je2MklZqnFAj9oESC5UzTF');

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,

    'uploads_path' => '@common/uploads/',
    
    'upload_path_profile_images' => '@uploads/profile/',
    'base_path_profile_images' => UPLOADS_PATH.'/profile/',
    
    'upload_path_voucher_images' => '@uploads/gift-vouchers/',
    'base_path_voucher_images' => UPLOADS_PATH.'/gift-vouchers/',

    'upload_path_voucher_images_bg' => '@uploads/gift-vouchers-bg/',
    'base_path_voucher_images_bg' => UPLOADS_PATH.'/gift-vouchers-bg/',
    
    'upload_path_products_images' => '@uploads/products/',
    'base_path_products_images' => UPLOADS_PATH.'/products/',
    
    'upload_path_music_files' => '@uploads/music-files/',
    'base_path_music_files' => UPLOADS_PATH.'/music-files/',

    'upload_path_event_images' => '@uploads/event-images/',
    'base_path_event_images' => UPLOADS_PATH.'/event-images/',

    'upload_path_menu_images' => '@uploads/menu-images/',
    'base_path_menu_images' => UPLOADS_PATH.'/menu-images/',

    'upload_path_video_wishes' => '@uploads/video-wishes/',
    'base_path_video_wishes' => UPLOADS_PATH.'/video-wishes/',
    
    'upload_path_event_video' => '@uploads/video-event/',
    'base_path_event_video' => UPLOADS_PATH.'/video-event/',

    'base_path_barcode' => UPLOADS_PATH.'/barcodes/',
    
    'base_path_woohoo_images' => UPLOADS_PATH.'/woohoo-images/',
    
    'upload_path_image_template_files' =>'@uploads/event-bg-images/',
    'base_path_image_template_files' => UPLOADS_PATH.'/event-bg-images/',
    
    // test
/*          'woohooConfig'=>[
          'baseAPIURL'=>'https://sandbox.woohoo.in/',
          'clientUsername'=>'prezenty.sandbox@woohoo.in',
          'clientPassword'=>'woohoo@2021',
          'clientID'=>'88d7346c8674587bc95f8fbde2e33acd',
          'clientSecret'=>'1a33c6118d1e0c74f7a012991d0e4e39',
          'svc'=>'8060180010000178'
      ],*/

    // live
      'woohooConfig'=>[
        'baseAPIURL'=>'https://extapi12.woohoo.in/',
        'clientUsername'=>'saheed.aboobakar@prezenty.in',
        'clientPassword'=>'welcome@123',
        'clientID'=>'ea751e8955520c2c4d858e5436556d6f',
        'clientSecret'=>'06afd380b569998a37be700e0c150812',
        'svc'=>'2144380020000379'
    ],

    'decentroConfig'=>[
        'baseAPIURL'=>'https://in.decentro.tech/',
        'client_id'=>'prezenty_prod',
        'client_secret'=>'2YDOXdsxhQazuihuaZTwWI1NK3T7iIXx',
        'module_secret'=>'xuxLNcnxLBfklPBj4GUrtA0WJ6GupG63',
        'provider_secret'=>'SNk8CdOCQs5Wqd50ndvDAyG8lu7bHPCk',
        'account_module'=>'Jzf3Ae2CoofEHFmIvDuOailvY7UNUSbT',
        'master_account_alias' => 'decentro_account_ybl_4'
    ],      


];
