<?php

use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Helper\Helper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

if (!class_exists('Duitku_Config')) {
    require(JPATH_ROOT . '/components/com_jshopping/payments/pm_duitku/duitku-php/Duitku.php');
}
class pm_duitku extends PaymentRoot
{

    function showPaymentForm($params, $pmconfigs)
    {
        include(dirname(__FILE__) . "/paymentform.php");
    }

    //function call in admin
    function showAdminFormParams($params)
    {
        $array_params = array('merchantCode', 'secretKey', 'urlRedirect', 'paymentMethod', 'transaction_end_status', 'transaction_failed_status', 'devMode', 'devUrl');

        foreach ($array_params as $key) {
            if (!isset($params[$key])) $params[$key] = '';
        }
        if (!isset($params['address_override'])) $params['address_override'] = 0;
        if (!isset($params['devMode'])) $params['devMode'] = 0;

        $orders = JSFactory::getModel('orders'); //admin model
        include(dirname(__FILE__) . "/adminparamsform.php");
    }

    function checkTransaction($pmconfigs, $order, $act)
    {
        Helper::saveToLog("duitku_debug.log", "=== checkTransaction called - Act: $act, Order ID: " . ($order ? $order->order_id : 'null'));

        $input = Factory::getApplication()->input;

        // Log all input parameters
        $allGet = $input->get->getArray();
        $allPost = $input->post->getArray();
        Helper::saveToLog("duitku_debug.log", "GET parameters: " . print_r($allGet, true));
        Helper::saveToLog("duitku_debug.log", "POST parameters: " . print_r($allPost, true));

        // Check both GET and POST for parameters
        $resultCode = $input->get('resultCode') ?: $input->post->get('resultCode');
        $merchantOrderId = $input->get('merchantOrderId') ?: $input->post->get('merchantOrderId');
        $reference = $input->get('reference') ?: $input->post->get('reference');

        Helper::saveToLog("duitku_debug.log", "Extracted - resultCode: $resultCode, merchantOrderId: $merchantOrderId, reference: $reference");

        if (empty($resultCode) || empty($merchantOrderId)) {
            Helper::saveToLog("duitku_debug.log", "Missing required parameters - resultCode: '$resultCode' or merchantOrderId: '$merchantOrderId'");
            return FALSE;
        }

        $merchantcode = $pmconfigs['merchantCode'];
        $secretkey = $pmconfigs['secretKey'];
        $urlredirect = $pmconfigs['urlRedirect'];

        if ($order) {
            Helper::saveToLog("duitku_debug.log", "Processing payment for order: " . $order->order_id);

            if ($resultCode == '00') {
                Helper::saveToLog("duitku_debug.log", "Payment SUCCESS - resultCode is 00");
                return array(1, 'Payment Successful', $reference);
            } else {
                Helper::saveToLog("duitku_debug.log", "Payment FAILED - resultCode: $resultCode");
                return array(0, 'Payment failed with code: ' . $resultCode);
            }
        } else {
            Helper::saveToLog("duitku_debug.log", "Order object is NULL");
            return FALSE;
        }
    }

    function showEndForm($pmconfigs, $order)
    {
        Helper::saveToLog("duitku_debug.log", "showEndForm called - Order ID: " . $order->order_id . ", Order Number: " . $order->order_number);

        $jshopConfig = JSFactory::getConfig();
        $pm_method = $this->getPmMethod();

        $paymentMethod = $pmconfigs['paymentMethod'];
        $merchantcode = $pmconfigs['merchantCode'];
        $secretkey = $pmconfigs['secretKey'];
        $urlredirect = $pmconfigs['urlRedirect'];

        $amount = $this->fixOrderTotal($order);
        $ordernumber = $order->order_number;
        $orderId = $order->order_id;
        $merchantUserInfo = $order->email;
        $signature = md5($merchantcode . $ordernumber . intval($amount) . $secretkey);

        Helper::saveToLog("duitku_debug.log", "Payment params - Amount: $amount, Order Number: $ordernumber, Signature: $signature");

        $uri = Uri::getInstance();
        $liveurlhost = $uri->toString(array("scheme", 'host', 'port'));
        
        // Use development URL if development mode is enabled
        if (!empty($pmconfigs['devMode']) && !empty($pmconfigs['devUrl'])) {
            $liveurlhost = rtrim($pmconfigs['devUrl'], '/');
            Helper::saveToLog("duitku_debug.log", "Development mode enabled - Using URL: " . $liveurlhost);
        }
        
        $params = array(
            'merchantCode' => $merchantcode,
            'paymentAmount' => intval($amount),
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $ordernumber,
            'productDetails' => 'Order : ' . $ordernumber,
            'additionalParam' => '',
            'merchantUserInfo' => $merchantUserInfo,
            'callbackUrl' => $liveurlhost . "/components/com_jshopping/payments/pm_duitku/callback.php?js_paymentclass=".$pm_method->payment_class."&custom=".$orderId,
            'returnUrl' => $liveurlhost . Helper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&custom=" . $orderId . "&js_paymentclass=" . $pm_method->payment_class),
            'signature' => $signature,
        );

        Helper::saveToLog("duitku_debug.log", "Duitku API params: " . print_r($params, true));

        try {
            $redirUrl = Duitku_VtWeb::getRedirectionUrl($urlredirect, $params);
            Helper::saveToLog("duitku_debug.log", "Redirect URL received: " . $redirUrl);
            Factory::getApplication()->redirect($redirUrl);
        } catch (Exception $e) {
            Helper::saveToLog("duitku_debug.log", "Duitku API Error: " . $e->getMessage());
            echo $e->getMessage();
            die();
        }
    }

    function getUrlParams($pmconfigs)
    {
        Helper::saveToLog("duitku_debug.log", "getUrlParams called");

        $input = Factory::getApplication()->input;
        $params = array();
        $params['order_id'] = $input->getInt("custom");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;

        Helper::saveToLog("duitku_debug.log", "getUrlParams returning: " . print_r($params, true));

        return $params;
    }

    function fixOrderTotal($order)
    {
        $total = $order->order_total;
        if ($order->currency_code_iso == 'HUF') {
            $total = round($total);
        } else {
            $total = number_format($total, 2, '.', '');
        }
        return $total;
    }
}
