# Duitku Payment Plugin for JoomShopping

A comprehensive payment gateway integration for JoomShopping that enables secure payments through Duitku's platform.

## Features

- Secure payment processing through Duitku API
- Multiple payment methods support with POP API (Credit Card, QRIS, Paylater, E-money, VA, etc.)
- Real-time transaction status updates
- Comprehensive logging for debugging
- Easy configuration through JoomShopping admin panel
- Production and sandbox environment support with local development options

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
   - **API Key**: Your Duitku API key
   - **Environment**: Choose Sandbox (testing) or Production (live)
   - **Development URL**: (Optional) Your ngrok/tunnel URL for local development
   - **Transaction End Status**: Order status for successful payments
   - **Transaction Failed Status**: Order status for failed payments

## Duitku Admin Panel Setup

> **Note:** The latest Duitku API version does not require you to set the callback URL in the plugin admin panel. The plugin automatically handles callbacks correctly.

## Supported Payment Methods

This plugin supports all payment methods available via Duitku POP API, including:

### Credit Card

- Visa/Mastercard/JCB

### Virtual Accounts

- BNI
- CIMB
- Maybank
- Permata
- ATM Bersama
- And more

### Retail Outlets

- Alfamart
- Indomaret

### E-Banking

- BCA Klikpay
- Mandiri Clickpay
- CIMB Clicks

### QRIS

- Shopeepay
- Gudang Voucher
- Nusapay

### Paylater

- Indodana
- Atome

### E-money

- ShopeePay
- Jenius Pay

> For a full and up-to-date list, refer to the [Duitku POP API documentation](https://docs.duitku.com/pop/).

## Requirements

- Joomla 5.x
- JoomShopping component
- PHP 7.4 or higher
- SSL certificate (recommended for production)

## File Structure

```
pm_duitku/
├── pm_duitku.xml          # Extension manifest
├── install.php            # Installation script
├── pm_duitku.php          # Main payment class
├── Config.php             # Configuration class for API environments
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
- **Callback not working?** Ensure your server is accessible from the internet
- **Installation fails?** Ensure JoomShopping component is installed first

## Support

- **Documentation**: [Duitku Developer Docs](https://docs.duitku.com)
- **Issues**: Report bugs via GitHub Issues
- **Duitku Support**: https://duitku.com

## License

GNU General Public License version 2 or later

## Changelog

### Version 1.1.0

- Support for all Duitku POP API payment methods, including QRIS, Paylater, and E-money
- Improved environment handling with automatic sandbox/production detection
- No callback URL configuration needed for latest API version
- Enhanced local development support with ngrok/tunnels

### Version 1.0.0

- Initial release
- Support for multiple payment methods
- Callback handling
- Admin configuration panel
- Transaction logging
