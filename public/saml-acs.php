<?php
require __DIR__ . '/../vendor/autoload.php';

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Auth;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

session_start();

try {
    $settings = require __DIR__ . '/settings.php';
    $auth = new Auth($settings);
    
    // Process SAML Response
    $auth->processResponse();
    
    if (!$auth->isAuthenticated()) {
        $errors = $auth->getErrors();
        if (!empty($errors)) {
            echo '<h1>SAML Authentication Error</h1>';
            echo '<p>The following errors occurred during authentication:</p>';
            echo '<ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '<p>Last error reason: ' . htmlspecialchars($auth->getLastErrorReason()) . '</p>';
            echo '<p><a href="/">Return to Home</a></p>';
            exit;
        }
    }
    
    // Authentication successful
    $_SESSION['authenticated'] = true;
    $_SESSION['username'] = $auth->getNameId();
    $_SESSION['auth_method'] = 'SAML';
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
    
    // Store SAML attributes
    $attributes = $auth->getAttributes();
    $_SESSION['saml_attributes'] = $attributes;
    
    // Redirect to the application
    $relayState = $_POST['RelayState'] ?? '/';
    header('Location: ' . $relayState);
    exit;
    
} catch (Exception $e) {
    echo '<h1>SAML Error</h1>';
    echo '<p>An error occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="/">Return to Home</a></p>';
}
