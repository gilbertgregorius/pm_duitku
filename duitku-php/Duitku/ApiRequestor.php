<?php

class Duitku_ApiRequestor
{

  public static function get($url, $data_hash, $headers = [])
  {
    return self::remoteCall($url, $data_hash, false, $headers);
  }

  public static function post($url, $data_hash, $headers = [])
  {
    return self::remoteCall($url, $data_hash, true, $headers);
  }

  public static function remoteCall($url, $data_hash, $post = true, $headers = [])
  {
    $ch = curl_init();
    $httpHeaders = [];

    curl_setopt($ch, CURLOPT_URL, $url);

    foreach ($headers as $key => $value) {
      $httpHeaders[] = $key . ': ' . $value;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
    

    if ($post) {
      curl_setopt($ch, CURLOPT_POST, 1);
      if ($data_hash) {
        $body = json_encode($data_hash);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
      } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
      }
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);

    if ($result === FALSE) {
      throw new Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));
    } else {
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $result_array = json_decode($result);
      if ($httpcode != 200) {
        $message = $result;
        throw new Exception($message, $httpcode);
      } else {
        return $result_array;
      }
    }
  }
}
