<?php
// thesmsbuddy.com

namespace common\components;

class Sms {

  public static function send($mobile, $message, $template_id, $sender="COCOAL")
  {
    $endPoint = 'http://thesmsbuddy.com/api/v1/sms/send';
    $curl = curl_init();

    $params = [
      'key' => '5uE8xeTySAptrw3zXLIUZ3mjMv6wdWsR', // TODO: move this to params
      'type' => 1,
      'to' => $mobile,
      'sender' => $sender,
      'message' => $message,
      'template_id' => $template_id
    ];

    curl_setopt_array($curl, [
      CURLOPT_URL => $endPoint . '?' . http_build_query($params),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
  }
}