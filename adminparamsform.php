<?php

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Administrator\Helper\HelperAdmin;

defined('_JEXEC') or die('Restricted access');
?>
<div class="col100">
  <fieldset class="adminform">
    <table class="admintable" width="100%">
      <tr>
        <td style="width:250px;" class="key">
          <?php echo 'Merchant Code'; ?>
        </td>
        <td>
          <input type="text" class="inputbox form-control" name="pm_params[merchantCode]" size="45" value="<?php echo htmlspecialchars($params['merchantCode']); ?>" />
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo 'API Key'; ?>
        </td>
        <td>
          <input type="text" class="inputbox form-control" name="pm_params[apiKey]" size="45" value="<?php echo htmlspecialchars($params['apiKey']); ?>" />
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo 'Environment'; ?>
        </td>
        <td>
          <select name="pm_params[environment]" id="environment" class="inputbox custom-select">
            <option value="sandbox" <?php if (isset($params['environment']) && $params['environment'] == 'sandbox') echo "selected";
                                    elseif (!isset($params['environment'])) echo "selected"; ?>>Sandbox (Testing)</option>
            <option value="production" <?php if (isset($params['environment']) && $params['environment'] == 'production') echo "selected"; ?>>Production (Live)</option>
          </select>
        </td>
      </tr>
      <tr id="devUrlRow" style="<?php echo (!isset($params['environment']) || $params['environment'] == 'sandbox') ? '' : 'display:none;'; ?>">
        <td class="key">
          <?php echo 'Development URL'; ?>
        </td>
        <td>
          <input type="text" class="inputbox form-control" name="pm_params[devUrl]" id="devUrl" size="50" value="<?php echo htmlspecialchars($params['devUrl']); ?>" placeholder="https://abc123.ngrok.io" />
          <br><small>Your ngrok or tunnel URL for local development (e.g., https://abc123.ngrok.io) - no trailing slash</small>
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo Text::_('JSHOP_TRANSACTION_END'); ?>
        </td>
        <td>
          <?php
          echo HTMLHelper::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class="inputbox custom-select" size="1"', 'status_id', 'name', $params['transaction_end_status']);
          // echo " " . HelperAdmin::tooltip(Text::_('JSHOP_DUITKU_TRANSACTION_END_DESCRIPTION'));
          ?>
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo Text::_('JSHOP_TRANSACTION_FAILED'); ?>
        </td>
        <td>
          <?php
          echo HTMLHelper::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class="inputbox custom-select" size="1"', 'status_id', 'name', $params['transaction_failed_status']);
          // echo " " . HelperAdmin::tooltip(Text::_('JSHOP_DUITKU_TRANSACTION_FAILED_DESCRIPTION'));
          ?>
        </td>
      </tr>
    </table>
  </fieldset>
</div>
<div class="clr"></div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const environmentSelect = document.getElementById('environment');
    const devUrlRow = document.getElementById('devUrlRow');

    if (environmentSelect && devUrlRow) {
      // Function to toggle development URL visibility
      function toggleDevUrl() {
        const isProduction = environmentSelect.value === 'production';
        devUrlRow.style.display = isProduction ? 'none' : '';
      }

      // Set initial state
      toggleDevUrl();

      // Listen for changes
      environmentSelect.addEventListener('change', toggleDevUrl);
    }
  });
</script>