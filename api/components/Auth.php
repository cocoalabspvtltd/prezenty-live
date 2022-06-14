<?php
namespace api\components;

use Yii;
use ReallySimpleJWT\Token;

class Auth {

  public static function validateToken()
  {
      $secret = SECRET_KEY;

      $headers = Yii::$app->request->headers;
      $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');

      if(!$api_token) {
            $msg = "Authentication failed";
            $success = false;

            return [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
      }

      $token = self::getBearerToken($api_token);

      $validateToken = Token::validate($token, $secret);

      if(!$validateToken) {
          $msg = "Authentication failed";
          $success = false;
          return [
              'message' => $msg,
              'statusCode' => 200,
              'success' => $success
          ];
      }

      return [];

      // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      // Yii::$app->response->content = $data;
      // Yii::$app->response->send();
      // die();
  }

  private static function getBearerToken($headers) {
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
