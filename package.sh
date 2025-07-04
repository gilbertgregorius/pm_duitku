#!/bin/bash

# Duitku Payment Plugin Packager Script
# This script creates an installable ZIP package for the Duitku payment plugin

echo "Creating Duitku Payment Plugin Package..."

# Get current directory name for package naming
DIR_NAME=$(basename "$PWD")
PACKAGE_NAME="${DIR_NAME}.zip"

# Remove any existing package
if [ -f "$PACKAGE_NAME" ]; then
    rm "$PACKAGE_NAME"
    echo "Removed existing package file"
fi

# Create the ZIP package
echo "Creating ZIP package..."
zip -r "$PACKAGE_NAME" . -x "*.DS_Store" "*.git*" "package.sh" "*.zip" "README.md"

echo ""
echo "Package created successfully: $PACKAGE_NAME"
echo "File location: $PWD/$PACKAGE_NAME"
echo ""
echo "Installation instructions:"
echo "1. Go to Extensions > Install"
echo "2. Choose 'Upload Package File'"
echo "3. Select the file: $PACKAGE_NAME"
echo "4. Click Install"
echo ""
echo "Configure the payment method in JoomShopping > Payment Methods after installation!"
