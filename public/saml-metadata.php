<?php
require __DIR__ . '/../vendor/autoload.php';

use OneLogin\Saml2\Settings;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

try {
    $settings = require __DIR__ . '/../saml/settings.php';
    $settingsObj = new Settings($settings);
    
    $metadata = $settingsObj->getSPMetadata();
    $errors = [];
    
    if (empty($errors)) {
        header('Content-Type: text/xml');
        echo $metadata;
    } else {
        header('Content-Type: text/html');
        echo '<h1>SP Metadata Error</h1>';
        echo '<p>The following errors were found in the SP configuration:</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
    }
} catch (Exception $e) {
    header('Content-Type: text/html');
    echo '<h1>Metadata Generation Error</h1>';
    echo '<p>An error occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
