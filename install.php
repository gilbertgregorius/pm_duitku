<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

class PlgSystemDuitkuInstallerScript
{
    public function install($parent)
    {
        $app = Factory::getApplication();

        try {
            // Copy payment files to JoomShopping directory
            $sourceDir = $parent->getParent()->getPath('source');
            $targetDir = JPATH_ROOT . '/components/com_jshopping/payments/pm_duitku';

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $files = ['pm_duitku.php', 'adminparamsform.php', 'callback.php', 'paymentform.php'];

            foreach ($files as $file) {
                if (file_exists($sourceDir . '/' . $file)) {
                    copy($sourceDir . '/' . $file, $targetDir . '/' . $file);
                }
            }

            // Copy duitku-php folder
            $this->copyDirectory($sourceDir . '/duitku-php', $targetDir . '/duitku-php');

            // Insert payment method into database (SQL in XML doesn't work)
            $db = Factory::getDbo();
            $query = "INSERT INTO `#__jshopping_payment_method` 
                (`payment_code`, `payment_class`, `scriptname`, `payment_publish`, `payment_ordering`, `payment_type`, `price`, `price_type`, `tax_id`, `show_descr_in_email`, `name_en-GB`, `name_de-DE`) 
                VALUES 
                ('DUITKU', 'pm_duitku', 'pm_duitku', 0, 0, 2, 0.00, 1, 1, 0, 'Duitku Payment Gateway', 'Duitku Payment Gateway')";

            $db->setQuery($query);
            $db->execute();

            $app->enqueueMessage('Duitku payment files copied and database updated!', 'success');
            return true;
        } catch (Exception $e) {
            $app->enqueueMessage('Installation error: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    private function copyDirectory($source, $destination)
    {
        if (!is_dir($source)) return false;

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
