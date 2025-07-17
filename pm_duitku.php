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

    function showAdminFormParams($params)
    {
        $array_params = array('merchantCode', 'apiKey', 'environment', 'paymentMethod', 'transaction_end_status', 'transaction_failed_status', 'devUrl');

        foreach ($array_params as $key) {
            if (!isset($params[$key])) $params[$key] = '';
        }

        if (!isset($params['environment']) || empty($params['environment'])) {
            $params['environment'] = 'sandbox';
        }
        if (!isset($params['address_override'])) $params['address_override'] = 0;

        $orders = JSFactory::getModel('orders'); // admin model
        include(dirname(__FILE__) . "/adminparamsform.php");
    }

    function checkTransaction($pmconfigs, $order, $act)
    {
        Helper::saveToLog("duitku.log", "INFO: checkTransaction called - Act: $act, Order ID: " . ($order ? $order->order_id : 'null'));

        try {
            $notification = new Duitku_Notification();

            if (!$notification->validateSignature($pmconfigs['merchantCode'], $pmconfigs['apiKey'])) {
                Helper::saveToLog("duitku.log", "WARNING: Signature validation failed");
                return FALSE;
            }

            if (empty($notification->resultCode) || empty($notification->merchantOrderId)) {
                Helper::saveToLog("duitku.log", "WARNING: Missing required notification fields");
                return FALSE;
            }

            if ($order) {
                if ($notification->isSuccess()) {
                    Helper::saveToLog("duitku.log", "INFO: Payment SUCCESS for order " . $order->order_id);
                    return array(1, 'Payment Successful', $notification->reference);
                } elseif ($notification->isFailed()) {
                    Helper::saveToLog("duitku.log", "WARNING: Payment FAILED for order " . $order->order_id . " - Code: " . $notification->resultCode);
                    return array(0, 'Payment failed with code: ' . $notification->resultCode);
                } else {
                    Helper::saveToLog("duitku.log", "INFO: Payment PENDING for order " . $order->order_id . " - Code: " . $notification->resultCode);
                    return array(2, 'Payment pending with code: ' . $notification->resultCode);
                }
            } else {
                Helper::saveToLog("duitku.log", "WARNING: Order object is NULL");
                return FALSE;
            }
        } catch (Exception $e) {
            Helper::saveToLog("duitku.log", "ERROR: checkTransaction failed - " . $e->getMessage());
            return FALSE;
        }
    }

    function showEndForm($pmconfigs, $order)
    {
        Helper::saveToLog("duitku.log", "INFO: showEndForm called - Order ID: " . $order->order_id . ", Order Number: " . $order->order_number);

        $pm_method = $this->getPmMethod();
        $amount = $this->fixOrderTotal($order);
        $orderId = $order->order_id;
        $item_name = sprintf(Text::_('JSHOP_PAYMENT_NUMBER'), $order->order_number);
        $callbackBaseUrl = $this->getCallbackBaseUrl($pmconfigs);
        $customerDetail = $this->buildDuitkuCustomerDetail($order);
        $itemDetails = $this->buildDuitkuItemDetails($order);
        $merchantUserInfo = $order->email;
        if (!empty($order->user_id)) {
            try {
                $db = Factory::getDbo();
                $query = "SELECT `username` FROM `#__users` WHERE `id` = " . (int)$order->user_id;
                $db->setQuery($query);
                $username = $db->loadResult();
                if ($username) {
                    $merchantUserInfo = $username;
                }
            } catch (Exception $e) {
                Helper::saveToLog("duitku.log", "WARNING: Error getting username, using email - " . $e->getMessage());
            }
        }

        $params = array(
            'paymentAmount' => intval($amount),
            'merchantOrderId' => $order->order_number,
            'productDetails' => 'Order : ' . $order->order_number . ' - ' . $item_name,
            'merchantUserInfo' => $merchantUserInfo,
            'customerDetail' => $customerDetail,
            'itemDetails' => $itemDetails,
            'email' => $order->email,
            'phoneNumber' => $order->phone ?? '',
            'callbackUrl' => $callbackBaseUrl . "/components/com_jshopping/payments/pm_duitku/callback.php?js_paymentclass=" . $pm_method->payment_class . "&custom=" . $orderId,
            'returnUrl' => $callbackBaseUrl . Helper::SEFLink("/index.php?option=com_jshopping&controller=checkout&task=step7&act=return&custom=" . $orderId . "&js_paymentclass=" . $pm_method->payment_class)
        );

        try {
            $headers = Duitku_HeaderGenerator::generate($pmconfigs['merchantCode'], $pmconfigs['apiKey']);
            $environment = isset($pmconfigs['environment']) ? DuitkuConfig::validateEnvironment($pmconfigs['environment']) : 'sandbox';
            $apiUrl = DuitkuConfig::getUrl($environment);
            Helper::saveToLog("duitku.log", "INFO: Using environment: " . $environment);

            $redirUrl = Duitku_POP::createInvoice($apiUrl, $params, $headers);
            Helper::saveToLog("duitku.log", "INFO: Redirect URL received, redirecting to payment page");

            header("Location: " . $redirUrl);
            exit();
        } catch (Exception $e) {
            Helper::saveToLog("duitku.log", "ERROR: Duitku POP API failed - " . $e->getMessage());
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

        // Development URL
        $environment = isset($pmconfigs['environment']) ? $pmconfigs['environment'] : 'sandbox';
        if ($environment === 'sandbox' && !empty($pmconfigs['devUrl'])) {
            $callbackBaseUrl = rtrim($pmconfigs['devUrl'], '/');
        }

        Helper::saveToLog("duitku.log", "INFO: Callback Base URL: " . $callbackBaseUrl . " (Environment: " . $environment . ")");
        return $callbackBaseUrl;
    }

    function getUrlParams($pmconfigs)
    {
        $input = Factory::getApplication()->input;
        $params = array();
        $params['order_id'] = $input->getInt("custom");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;

        Helper::saveToLog("duitku.log", "INFO: getUrlParams - Order ID: " . $params['order_id']);

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

    private function buildDuitkuCustomerDetail($order)
    {
        $billingCountryCode = $this->getCountryCode($order->country ?? '');
        $shippingCountryCode = $this->getCountryCode($order->d_country ?? $order->country ?? '');

        return array(
            'firstName' => $order->f_name,
            'lastName' => $order->l_name,
            'email' => $order->email,
            'phoneNumber' => $order->phone,
            'billingAddress' => array(
                'firstName' => $order->f_name,
                'lastName' => $order->l_name,
                'address' => trim(($order->street) . ' ' . ($order->street_nr)),
                'city' => $order->city,
                'postalCode' => $order->zip,
                'phone' => $order->phone,
                'countryCode' => $billingCountryCode
            ),
            'shippingAddress' => array(
                'firstName' => $order->d_f_name ?? $order->f_name,
                'lastName' => $order->d_l_name ?? $order->l_name,
                'address' => trim(($order->d_street ?? $order->street) . ' ' . ($order->d_street_nr ?? $order->street_nr)),
                'city' => $order->d_city ?? $order->city,
                'postalCode' => $order->d_zip ?? $order->zip,
                'phone' => $order->phone,
                'countryCode' => $shippingCountryCode
            )
        );
    }

    private function buildDuitkuItemDetails($order)
    {
        $itemDetails = array();

        $db = Factory::getDbo();
        $query = "SELECT * FROM `#__jshopping_order_item` WHERE `order_id` = " . (int)$order->order_id;
        $db->setQuery($query);
        $orderItems = $db->loadObjectList();
        Helper::saveToLog("duitku.log", "INFO: Loaded " . count($orderItems) . " order items from database");

        if (!empty($orderItems)) {
            foreach ($orderItems as $item) {
                $itemDetails[] = array(
                    'name' => $item->product_name ?? 'Product',
                    'quantity' => (int)($item->product_quantity ?? 1),
                    'price' => (int)round($item->product_item_price ?? 0)
                );
            }
        }

        // Shipping
        if (isset($order->order_shipping) && $order->order_shipping > 0) {
            $itemDetails[] = array(
                'name' => 'Shipping',
                'quantity' => 1,
                'price' => (int)round($order->order_shipping)
            );
        }

        // Calculate and verify total
        $itemsTotal = 0;
        foreach ($itemDetails as $item) {
            $itemsTotal += $item['price'] * $item['quantity'];
        }

        // The total should match exactly (subtotal + shipping = order total)
        if (abs($itemsTotal - $order->order_total) > 1) {
            Helper::saveToLog("duitku.log", "WARNING: Item total ($itemsTotal) doesn't match order total (" . ($order->order_total) . ")");
        } else {
            Helper::saveToLog("duitku.log", "INFO: Item total verified: $itemsTotal IDR");
        }

        return $itemDetails;
    }

    private function getCountryCode($countryId)
    {
        if (empty($countryId)) return '';

        try {
            $db = Factory::getDbo();
            $query = "SELECT `country_code_2` FROM `#__jshopping_countries` WHERE `country_id` = " . (int)$countryId;
            $db->setQuery($query);
            $result = $db->loadResult();
            return $result ?? '';
        } catch (Exception $e) {
            Helper::saveToLog("duitku.log", "WARNING: Error getting country code - " . $e->getMessage());
            return '';
        }
    }
}
