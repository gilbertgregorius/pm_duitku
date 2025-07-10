<?php

use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Helper\Helper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

if (!class_exists('Duitku_Notification')) {
    require(dirname(__FILE__) . '/duitku-php/Duitku.php');
}

if (!class_exists('DuitkuConfig')) {
    require(dirname(__FILE__) . '/duitku-php/Config.php');
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
        $array_params = array('merchantCode', 'apiKey', 'environment', 'paymentMethod', 'transaction_end_status', 'transaction_failed_status', 'devUrl');

        foreach ($array_params as $key) {
            if (!isset($params[$key])) $params[$key] = '';
        }
        // Set default environment to sandbox if not set
        if (!isset($params['environment']) || empty($params['environment'])) {
            $params['environment'] = 'sandbox';
        }
        if (!isset($params['address_override'])) $params['address_override'] = 0;

        $orders = JSFactory::getModel('orders'); //admin model
        include(dirname(__FILE__) . "/adminparamsform.php");
    }

    function checkTransaction($pmconfigs, $order, $act)
    {
        Helper::saveToLog("duitku_debug.log", "=== checkTransaction called - Act: $act, Order ID: " . ($order ? $order->order_id : 'null'));

        try {
            $notification = new Duitku_Notification();

            Helper::saveToLog("duitku_debug.log", "Notification created - Result Code: " . $notification->resultCode .
                ", Merchant Order ID: " . $notification->merchantOrderId .
                ", Reference: " . $notification->reference);

            if (!$notification->validateSignature($pmconfigs['merchantCode'], $pmconfigs['apiKey'])) {
                Helper::saveToLog("duitku_debug.log", "Signature validation failed");
                return FALSE;
            }

            if (empty($notification->resultCode) || empty($notification->merchantOrderId)) {
                Helper::saveToLog("duitku_debug.log", "Missing required notification fields");
                return FALSE;
            }

            if ($order) {
                Helper::saveToLog("duitku_debug.log", "Processing payment for order: " . $order->order_id);

                if ($notification->isSuccess()) {
                    Helper::saveToLog("duitku_debug.log", "Payment SUCCESS - resultCode is 00");
                    return array(1, 'Payment Successful', $notification->reference);
                } elseif ($notification->isFailed()) {
                    Helper::saveToLog("duitku_debug.log", "Payment FAILED - resultCode: " . $notification->resultCode);
                    return array(0, 'Payment failed with code: ' . $notification->resultCode);
                } else {
                    Helper::saveToLog("duitku_debug.log", "Payment PENDING - resultCode: " . $notification->resultCode);
                    return array(2, 'Payment pending with code: ' . $notification->resultCode);
                }
            } else {
                Helper::saveToLog("duitku_debug.log", "Order object is NULL");
                return FALSE;
            }
        } catch (Exception $e) {
            Helper::saveToLog("duitku_debug.log", "checkTransaction error: " . $e->getMessage());
            return FALSE;
        }
    }

    function showEndForm($pmconfigs, $order)
    {
        Helper::saveToLog("duitku_debug.log", "=== showEndForm called - Order ID: " . $order->order_id . ", Order Number: " . $order->order_number . " ===");

        $pm_method = $this->getPmMethod();
        $amount = $this->fixOrderTotal($order);
        $orderId = $order->order_id;
        $item_name = sprintf(Text::_('JSHOP_PAYMENT_NUMBER'), $order->order_number);
        $callbackBaseUrl = $this->getCallbackBaseUrl($pmconfigs);

        $params = array(
            'paymentAmount' => intval($amount),
            'merchantOrderId' => $order->order_number,
            'productDetails' => 'Order : ' . $order->order_number . ' - ' . $item_name,
            'email' => $order->email,
            'callbackUrl' => $callbackBaseUrl . "/components/com_jshopping/payments/pm_duitku/callback.php?js_paymentclass=" . $pm_method->payment_class . "&custom=" . $orderId,
            'returnUrl' => $callbackBaseUrl . Helper::SEFLink("/index.php?option=com_jshopping&controller=checkout&task=step7&act=return&custom=" . $orderId . "&js_paymentclass=" . $pm_method->payment_class)
        );

        Helper::saveToLog("duitku_debug.log", "POP API params: " . print_r($params, true));

        try {
            $headers = Duitku_HeaderGenerator::generate($pmconfigs['merchantCode'], $pmconfigs['apiKey']);
            $environment = isset($pmconfigs['environment']) ? DuitkuConfig::validateEnvironment($pmconfigs['environment']) : 'sandbox';
            $apiUrl = DuitkuConfig::getUrl($environment);
            Helper::saveToLog("duitku_debug.log", "Using environment: " . $environment . ", API URL: " . $apiUrl);

            $redirUrl = Duitku_POP::createInvoice($apiUrl, $params, $headers);
            Helper::saveToLog("duitku_debug.log", "Redirect URL received: " . $redirUrl);

            header("Location: " . $redirUrl);
            exit();
        } catch (Exception $e) {
            Helper::saveToLog("duitku_debug.log", "Duitku POP API Error: " . $e->getMessage());
            echo "Payment processing error: " . $e->getMessage();
            return;
        }
    }

    function getCallbackBaseUrl($pmconfigs)
    {
        $uri = Uri::getInstance();
        $scheme = $uri->toString(['scheme']);
        $host = $uri->toString(['host', 'port']);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        if ($basePath === '/') $basePath = '';

        $callbackBaseUrl = $scheme . '://' . $host . $basePath;

        // Use development URL if environment is sandbox and devUrl is provided
        $environment = isset($pmconfigs['environment']) ? $pmconfigs['environment'] : 'sandbox';
        if ($environment === 'sandbox' && !empty($pmconfigs['devUrl'])) {
            $callbackBaseUrl = rtrim($pmconfigs['devUrl'], '/');
        }

        Helper::saveToLog("duitku_debug.log", "Callback Base URL: " . $callbackBaseUrl . " (Environment: " . $environment . ")");
        return $callbackBaseUrl;
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
