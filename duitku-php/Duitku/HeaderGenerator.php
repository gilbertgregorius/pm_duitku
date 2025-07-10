<?php

use Joomla\Component\Jshopping\Site\Helper\Helper;

class Duitku_HeaderGenerator
{
    public static function generate($merchantCode, $apiKey)
    {
        if (empty($merchantCode) || empty($apiKey)) {
            throw new Exception('Merchant Code and API Key are required for header generation');
        }

        $timestamp = self::getJakartaTimestamp();
        $signature = self::generateSignature($merchantCode, $timestamp, $apiKey);

        return [
            'x-duitku-signature' => $signature,
            'x-duitku-timestamp' => $timestamp,
            'x-duitku-merchantcode' => $merchantCode,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }

    private static function getJakartaTimestamp()
    {
        $jakarta_tz = new DateTimeZone('Asia/Jakarta');
        $datetime = new DateTime('now', $jakarta_tz);
        return $datetime->getTimestamp() * 1000;
    }

    private static function generateSignature($merchantCode, $timestamp, $apiKey)
    {
        $signature_string = $merchantCode . $timestamp . $apiKey;
        return hash('sha256', $signature_string);
    }

    public static function generateWithLogging($merchantCode, $apiKey)
    {
        $headers = self::generate($merchantCode, $apiKey);
        Helper::saveToLog("duitku_debug.log", "POP API Headers generated" . print_r($headers, true));
        return $headers;
    }

    public static function validateHeaders($headers)
    {
        $required = ['x-duitku-signature', 'x-duitku-timestamp', 'x-duitku-merchantcode'];

        foreach ($required as $header) {
            if (empty($headers[$header])) {
                return false;
            }
        }

        if (!preg_match('/^\d{13}$/', $headers['x-duitku-timestamp'])) {
            return false;
        }

        if (!preg_match('/^[a-f0-9]{64}$/', $headers['x-duitku-signature'])) {
            return false;
        }

        return true;
    }
}
