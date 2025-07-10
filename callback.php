<?php

/**
 * Duitku Callback Handler for JoomShopping
 */

// Log that callback was received
$logData = date('Y-m-d H:i:s') . " CALLBACK RECEIVED\n";
$logData .= "GET: " . print_r($_GET, true) . "\n";
$logData .= "POST: " . print_r($_POST, true) . "\n\n";

file_put_contents(__DIR__ . '/../../../../components/com_jshopping/log/duitku_callback.log', $logData, FILE_APPEND);

// Get payment parameters
$paymentClass = $_GET['js_paymentclass'] ?? $_POST['js_paymentclass'] ?? 'pm_duitku';
$customId = $_GET['custom'] ?? $_POST['custom'] ?? '';

// Get the current domain and path from the request
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Extract the base path (go up 4 levels from /path/components/com_jshopping/payments/duitku-payment-plugin-origin/callback.php)
$basePath = dirname(dirname(dirname(dirname(dirname($requestUri)))));
if ($basePath === '/') $basePath = '';

// Build the JoomShopping notification URL
$notifyUrl = $protocol . $host . $basePath . 
            '/index.php?option=com_jshopping&controller=checkout&task=step7' . 
            '&act=notify&js_paymentclass=' . $paymentClass . 
            '&custom=' . $customId . '&no_lang=1';

file_put_contents(
    __DIR__ . '/../../../../components/com_jshopping/log/duitku_callback.log',
    "DEBUG: requestUri=$requestUri, basePath=$basePath, notifyUrl=$notifyUrl\n",
    FILE_APPEND
);

// Prepare POST data with all Duitku parameters
$postData = array_merge($_GET, $_POST);

// Make a server-to-server POST request to JoomShopping
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $notifyUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Log the result
file_put_contents(
    __DIR__ . '/../../../../components/com_jshopping/log/duitku_callback.log',
    date('Y-m-d H:i:s') . " cURL response: HTTP $httpCode\n" . substr($response, 0, 500) . "\n\n",
    FILE_APPEND
);

// Respond to Duitku
echo ($httpCode == 200) ? "OK" : "ERROR";
