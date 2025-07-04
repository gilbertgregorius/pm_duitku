# Duitku Payment Plugin for JoomShopping

A comprehensive payment gateway integration for JoomShopping that enables secure payments through Duitku's platform.

## Features

- Secure payment processing through Duitku API
- Multiple payment methods support (Credit Card, VA BNI, VA CIMB, BCA Klikpay, etc.)
- Real-time transaction status updates
- Comprehensive logging for debugging
- Easy configuration through JoomShopping admin panel
- Production and sandbox environment support

## Installation

### Method 1: Download Release Package
1. Download the latest release ZIP file from GitHub releases
2. Go to **Extensions > Install** in your Joomla admin
3. Choose "Upload Package File"
4. Select the downloaded ZIP file and install

### Method 2: Build from Source
```bash
# Clone the repository
git clone https://github.com/yourusername/duitku-joomshopping-plugin.git
cd duitku-joomshopping-plugin

# Make package script executable
chmod +x package.sh

# Create installation package
./package.sh

# Upload the generated ZIP file via Extensions > Install
```

## Configuration

After installation, configure the plugin:

1. Go to **JoomShopping > Payment Methods**
2. Find and edit the **Duitku** payment method
3. Configure the required settings:
   - **Merchant Code**: Your Duitku merchant code
   - **Secret Key**: Your Duitku secret key
   - **URL Redirect**: Choose Sandbox or Production
   - **Payment Method**: Select specific payment method (VC, BK, MY, etc.)
   - **Transaction End Status**: Order status for successful payments
   - **Transaction Failed Status**: Order status for failed payments

## Duitku Admin Panel Setup

Set your callback URL in Duitku admin panel to:
```
https://yourdomain.com/components/com_jshopping/payments/pm_duitku/callback.php
```

## Supported Payment Methods

- **VC**: Credit Card
- **BK**: BCA Klikpay
- **MY**: Mandiri Clickpay
- **CK**: CIMB Clicks
- **BT**: VA Permata
- **A1**: VA ATM Bersama
- **I1**: VA BNI
- **B1**: VA CIMB
- **VA**: VA Maybank
- **FT**: Retail

## Requirements

- Joomla 4.x or 5.x
- JoomShopping component
- PHP 7.4 or higher
- SSL certificate (recommended for production)

## File Structure

```
pm_duitku/
├── pm_duitku.xml          # Extension manifest
├── install.php            # Installation script
├── pm_duitku.php          # Main payment class
├── adminparamsform.php    # Admin configuration form
├── callback.php           # Payment callback handler
├── paymentform.php        # Frontend payment form
├── TODO                   # Development notes
└── duitku-php/           # Duitku PHP SDK
    ├── Duitku.php
    └── Duitku/
        ├── ApiRequestor.php
        ├── Config.php
        └── VtWeb.php
```

## Development

### Building Package
```bash
chmod +x package.sh
./package.sh
```

### Testing
1. Install the package on a development site
2. Configure test credentials from Duitku
3. Test payment flow with different payment methods

## Troubleshooting

- **Payment method not showing?** Check file permissions and JoomShopping configuration
- **Callback not working?** Verify callback URL in Duitku admin panel
- **Installation fails?** Ensure JoomShopping component is installed first

## Support

- **Documentation**: [Duitku Developer Docs](https://docs.duitku.com)
- **Issues**: Report bugs via GitHub Issues
- **Duitku Support**: https://duitku.com

## License

GNU General Public License version 2 or later

## Changelog

### Version 1.0.0
- Initial release
- Support for multiple payment methods
- Callback handling
- Admin configuration panel
- Transaction logging
