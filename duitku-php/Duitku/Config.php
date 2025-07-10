<?php

defined('_JEXEC') or die('Restricted access');

/**
 * Duitku Payment Plugin Configuration
 * Centralized configuration for API URLs and environment settings
 */
class DuitkuConfig
{
    /**
     * Get API configuration based on environment
     * 
     * @param string $environment 'sandbox' or 'production'
     * @return array Configuration array with baseUrl and apiUrl
     */
    public static function getUrl($environment = 'sandbox')
    {
        $configs = [
            'sandbox' => 'https://api-sandbox.duitku.com/api/merchant/createInvoice',
            'production' => 'https://api-prod.duitku.com/api/merchant/createInvoice'
        ];

        return isset($configs[$environment]) ? $configs[$environment] : $configs['sandbox'];
    }

    /**
     * Check if environment is production
     * 
     * @param string $environment
     * @return bool
     */
    public static function isProduction($environment)
    {
        return $environment === 'production';
    }

    /**
     * Get available environments
     * 
     * @return array
     */
    public static function getEnvironments()
    {
        return [
            'sandbox' => 'Sandbox (Testing)',
            'production' => 'Production (Live)'
        ];
    }

    /**
     * Validate environment string
     * 
     * @param string $environment
     * @return string Valid environment string
     */
    public static function validateEnvironment($environment)
    {
        return in_array($environment, ['sandbox', 'production']) ? $environment : 'sandbox';
    }
}
