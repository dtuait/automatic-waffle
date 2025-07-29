<?php
require __DIR__ . '/../vendor/autoload.php';

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Auth;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

try {
    $settings = require __DIR__ . '/../saml/settings.php';
    $auth = new Auth($settings);
    
    echo "SAML Settings loaded successfully!\n";
    echo "Entity ID: " . $settings['idp']['entityId'] . "\n";
    echo "SSO URL: " . $settings['idp']['singleSignOnService']['url'] . "\n";
    echo "SP Entity ID: " . $settings['sp']['entityId'] . "\n";
    echo "SP ACS URL: " . $settings['sp']['assertionConsumerService']['url'] . "\n";
    
    // Generate SSO URL
    $ssoUrl = $auth->login(null, array(), false, false, true);
    echo "Generated SSO URL: " . $ssoUrl . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
