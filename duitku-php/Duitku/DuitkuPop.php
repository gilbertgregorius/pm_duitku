<?php

class Duitku_POP
{
  public static function createInvoice($url, $params, $headers)
  {
    $result = Duitku_ApiRequestor::post($url, $params, $headers);
    return $result->paymentUrl;
  }

  public static function checkTransactionStatus($url, $params, $headers)
  {
    $result = Duitku_ApiRequestor::post($url, $params, $headers);
    return $result;
  }
}
