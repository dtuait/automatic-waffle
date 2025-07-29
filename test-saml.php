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

    // Optionally fetch the IdP page to detect obvious error messages
    echo "Checking IdP response...\n";

    $caBundle = __DIR__ . '/sts_ait_dtu_ca.pem';
    $context = stream_context_create([
        'http' => ['method' => 'GET'],
        'ssl'  => [
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'cafile'            => file_exists($caBundle) ? $caBundle : null,
        ],
    ]);

    $html = @file_get_contents($ssoUrl, false, $context);
    if ($html === false) {
        $error = error_get_last();
        echo "✗ Failed to fetch SSO URL: " . ($error['message'] ?? 'unknown error') . "\n";
    } else {
        if (stripos($html, 'An error occurred') !== false) {
            echo "✗ IdP returned an error page\n";
        } else {
            echo "✓ IdP page loaded successfully\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
