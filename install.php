<?php

/**
 * Duitku Payment Plugin Installation Script
 * This script handles the installation of the Duitku payment plugin for JoomShopping
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PlgSystemPm_duitkuInstallerScript
{
    public function install($parent)
    {
        $db = Factory::getDbo();

        // Check if JoomShopping is installed
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from('#__extensions')
            ->where('element = ' . $db->quote('com_jshopping'))
            ->where('type = ' . $db->quote('component'));

        $db->setQuery($query);
        $jshopInstalled = (int)$db->loadResult();

        if (!$jshopInstalled) {
            Factory::getApplication()->enqueueMessage('JoomShopping component is required for this payment plugin to work.', 'warning');
            return false;
        }

        // Insert payment method if it doesn't exist
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from('#__jshopping_payment_method')
            ->where('payment_class = ' . $db->quote('pm_duitku'));

        $db->setQuery($query);
        $exists = (int)$db->loadResult();

        if (!$exists) {
            $query = $db->getQuery(true);
            $query->insert('#__jshopping_payment_method')
                ->columns('payment_class, payment_type, payment_publish, payment_ordering, payment_params, payment_code')
                ->values(
                    $db->quote('pm_duitku') . ', ' .
                        '2, ' .
                        '1, ' .
                        '1, ' .
                        $db->quote('{"merchantCode":"","apiKey":"","environment":"sandbox","paymentMethod":"","transaction_end_status":"C","transaction_failed_status":"P","address_override":0,"devUrl":""}') . ', ' .
                        $db->quote('DUITKU')
                );

            $db->setQuery($query);
            $db->execute();
        }

        Factory::getApplication()->enqueueMessage('Duitku Payment Plugin installed successfully!', 'message');
        Factory::getApplication()->enqueueMessage('Please configure the payment method in JoomShopping > Payment Methods.', 'info');

        return true;
    }

    public function uninstall($parent)
    {
        $db = Factory::getDbo();

        try {
            // Get payment method ID first
            $query = $db->getQuery(true);
            $query->select('payment_id')
                ->from('#__jshopping_payment_method')
                ->where('payment_class = ' . $db->quote('pm_duitku'));

            $db->setQuery($query);
            $paymentId = $db->loadResult();

            if ($paymentId) {
                // Check if payment method is used in any orders
                $query = $db->getQuery(true);
                $query->select('COUNT(*)')
                    ->from('#__jshopping_orders')
                    ->where('payment_method_id = ' . (int)$paymentId);

                $db->setQuery($query);
                $ordersCount = (int)$db->loadResult();

                if ($ordersCount > 0) {
                    // Don't delete if orders exist, just disable
                    $query = $db->getQuery(true);
                    $query->update('#__jshopping_payment_method')
                        ->set('payment_publish = 0')
                        ->where('payment_id = ' . (int)$paymentId);

                    $db->setQuery($query);
                    $db->execute();

                    Factory::getApplication()->enqueueMessage(
                        'Duitku Payment Plugin disabled (not deleted) because it has been used in ' . $ordersCount . ' orders.',
                        'info'
                    );
                } else {
                    // Safe to delete
                    $query = $db->getQuery(true);
                    $query->delete('#__jshopping_payment_method')
                        ->where('payment_id = ' . (int)$paymentId);

                    $db->setQuery($query);
                    $db->execute();

                    Factory::getApplication()->enqueueMessage('Duitku Payment Plugin removed from database.', 'message');
                }
            }

            // Clean up log files
            $logFiles = [
                JPATH_ROOT . '/components/com_jshopping/log/duitku_debug.log',
                JPATH_ROOT . '/components/com_jshopping/log/duitku_callback.log'
            ];

            foreach ($logFiles as $logFile) {
                if (file_exists($logFile)) {
                    unlink($logFile);
                }
            }

            // Remove the plugin directory
            $targetDir = JPATH_ROOT . '/components/com_jshopping/payments/pm_duitku';
            if (is_dir($targetDir)) {
                $this->removeDirectory($targetDir);
                Factory::getApplication()->enqueueMessage('Plugin files removed successfully!', 'message');
            }

            Factory::getApplication()->enqueueMessage('Duitku Payment Plugin uninstalled successfully!', 'message');
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage('Error during uninstallation: ' . $e->getMessage(), 'error');
        }

        return true;
    }

    public function update($parent)
    {
        Factory::getApplication()->enqueueMessage('Duitku Payment Plugin updated successfully!', 'message');
        return true;
    }

    public function preflight($type, $parent)
    {
        // Check minimum requirements
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            Factory::getApplication()->enqueueMessage('This plugin requires PHP 7.4 or higher.', 'error');
            return false;
        }

        return true;
    }

    /**
     * Helper method to recursively remove a directory
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * Helper method to recursively copy a directory
     */
    private function copyDirectory($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = array_diff(scandir($source), array('.', '..'));

        foreach ($files as $file) {
            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }

        return true;
    }
}
