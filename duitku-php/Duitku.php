<?php

if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  throw new Exception('PHP version >= 5.2.1 required');
}

if (!function_exists('curl_init')) {
  throw new Exception('Duitku needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Duitku needs the JSON PHP extension.');
}

require_once('Duitku/ApiRequestor.php');
require_once('Duitku/Config.php');
require_once('Duitku/DuitkuPop.php');
require_once('Duitku/HeaderGenerator.php');
require_once('Duitku/Notification.php');