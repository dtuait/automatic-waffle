<?php
require __DIR__ . '/vendor/autoload.php';

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Auth;

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

echo "Testing SAML Authentication...\n";

try {
    $settings = require __DIR__ . '/saml/settings.php';
    echo "✓ Settings loaded successfully\n";
    
    $auth = new Auth($settings);
    echo "✓ Auth object created\n";
    
    // Try to generate the SSO URL
    $ssoUrl = $auth->login(null, [], false, false, true);
    echo "✓ SSO URL generated: " . $ssoUrl . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
