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
          <?php echo 'Secret Key'; ?>
        </td>
        <td>
          <input type="text" class="inputbox form-control" name="pm_params[secretKey]" size="45" value="<?php echo htmlspecialchars($params['secretKey']); ?>" />
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo 'URL Redirect'; ?>
        </td>
        <td>
          <select name="pm_params[urlRedirect]" class="inputbox custom-select">
            <option value="https://sandbox.duitku.com/webapi" <?php if ($params['urlRedirect'] == 'https://sandbox.duitku.com/webapi') echo "selected"; ?>>Sandbox</option>
            <option value="https://passport.duitku.com/webapi" <?php if ($params['urlRedirect'] == 'https://passport.duitku.com/webapi') echo "selected"; ?>>Production</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo 'Payment Method'; ?>
        </td>
        <td>
          <select id="paymentTypeId" name="pm_params[paymentMethod]" class="inputbox custom-select">
            <option value="VC" <?php if ($params['paymentMethod'] == 'VC') echo "selected"; ?>>Credit Card</option>
            <option value="BK" <?php if ($params['paymentMethod'] == 'BK') echo "selected"; ?>>BCA Klikpay</option>
            <option value="MY" <?php if ($params['paymentMethod'] == 'MY') echo "selected"; ?>>Mandiri Clickpay</option>
            <option value="CK" <?php if ($params['paymentMethod'] == 'CK') echo "selected"; ?>>Cimb clicks</option>
            <option value="BT" <?php if ($params['paymentMethod'] == 'BT') echo "selected"; ?>>VA Permata</option>
            <option value="A1" <?php if ($params['paymentMethod'] == 'A1') echo "selected"; ?>>VA ATM Bersama</option>
            <option value="I1" <?php if ($params['paymentMethod'] == 'I1') echo "selected"; ?>>VA BNI</option>
            <option value="B1" <?php if ($params['paymentMethod'] == 'B1') echo "selected"; ?>>VA CIMB</option>
            <option value="VA" <?php if ($params['paymentMethod'] == 'VA') echo "selected"; ?>>VA Maybank</option>
            <option value="FT" <?php if ($params['paymentMethod'] == 'FT') echo "selected"; ?>>Ritel</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="key">
          <?php echo 'Development Mode'; ?>
        </td>
        <td>
          <input type="checkbox" id="devMode" name="pm_params[devMode]" value="1" <?php if (!empty($params['devMode'])) echo 'checked="checked"'; ?> />
          <label for="devMode">Enable for local development testing</label>
          <br><small>When enabled, use custom URL for callbacks instead of auto-detected domain</small>
        </td>
      </tr>
      <tr id="devUrlRow" style="<?php echo !empty($params['devMode']) ? '' : 'display:none;'; ?>">
        <td class="key">
          <?php echo 'Development URL'; ?>
        </td>
        <td>
          <input type="text" class="inputbox form-control" name="pm_params[devUrl]" id="devUrl" size="50" value="<?php echo htmlspecialchars($params['devUrl']); ?>" placeholder="https://abc123.ngrok.io" />
          <br><small>Your ngrok or tunnel URL (e.g., https://abc123.ngrok.io) - no trailing slash</small>
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
    const devModeCheckbox = document.getElementById('devMode');
    const devUrlRow = document.getElementById('devUrlRow');
    
    if (devModeCheckbox) {
        devModeCheckbox.addEventListener('change', function() {
            devUrlRow.style.display = this.checked ? '' : 'none';
        });
    }
});
</script>