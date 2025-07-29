<?php
require __DIR__ . '/../vendor/autoload.php';

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Auth;

// Load environment variables from .env if using vlucas/phpdotenv
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

session_start();

// Check if user is already authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Check for logout request
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: /');
        exit;
    }
    
    // Check for login request
    if (isset($_GET['login'])) {
        $authMethod = $_GET['method'] ?? 'cas';
        
        if ($authMethod === 'cas') {
            // CAS Authentication
            $host = $_ENV['CAS_HOST'] ?? getenv('CAS_HOST') ?: 'cas.example.com';
            $port = $_ENV['CAS_PORT'] ?? getenv('CAS_PORT') ?: 443;
            $context = $_ENV['CAS_CONTEXT'] ?? getenv('CAS_CONTEXT') ?: '/cas';
            $caCert = $_ENV['CAS_CA_CERT'] ?? getenv('CAS_CA_CERT');
            $baseUrl = $_ENV['SERVICE_BASE_URL'] ?? getenv('SERVICE_BASE_URL') ?: '';
            
            phpCAS::client(CAS_VERSION_2_0, $host, (int)$port, $context, $baseUrl);
            if ($caCert) {
                phpCAS::setCasServerCACert($caCert);
            } else {
                phpCAS::setNoCasServerValidation();
            }
            
            phpCAS::forceAuthentication();
            
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = phpCAS::getUser();
            $_SESSION['auth_method'] = 'CAS';
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            
        } elseif ($authMethod === 'saml') {
            // SAML Authentication
            try {
                $settings = require __DIR__ . '/../saml/settings.php';
                $auth = new Auth($settings);
                
                // Initiate SAML SSO
                $auth->login();
                exit;
                
            } catch (Exception $e) {
                $error = "SAML Error: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine();
                // Don't redirect, show the error on the login page
            }
        }
        
        // Only redirect if there was no error
        if (!isset($error)) {
            header('Location: /');
            exit;
        }
    }
    
    // Show login page
    showLoginPage($error ?? null);
    exit;
}

// User is authenticated, show the hello world page
showWelcomePage();

function showLoginPage($error = null) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Required - Hello World</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 400px;
                width: 90%;
            }
            h1 {
                color: #333;
                margin-bottom: 1rem;
            }
            p {
                color: #666;
                margin-bottom: 2rem;
            }
            .login-buttons {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            .btn-cas {
                background: #4CAF50;
                color: white;
            }
            .btn-saml {
                background: #2196F3;
                color: white;
            }
            .config-info {
                margin-top: 2rem;
                padding: 1rem;
                background: #f5f5f5;
                border-radius: 6px;
                font-size: 14px;
                color: #666;
            }
            <?php if (isset($error)): ?>
            .error {
                background: #ffebee;
                color: #c62828;
                padding: 1rem;
                border-radius: 6px;
                margin-bottom: 1rem;
            }
            <?php endif; ?>
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🌍 Hello World</h1>
            <p>Please authenticate to access the hello world page</p>
            
            <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="login-buttons">
                <a href="?login=1&method=cas" class="btn btn-cas">
                    🎫 Login with CAS
                </a>
                <a href="?login=1&method=saml" class="btn btn-saml">
                    🔐 Login with SAML (DTU ADFS)
                </a>
            </div>
            
            <div class="config-info">
                <strong>Configuration Status:</strong><br>
                CAS Host: <?php echo htmlspecialchars(($_ENV['CAS_HOST'] ?? getenv('CAS_HOST')) ?: 'Not configured'); ?><br>
                SAML IDP: <?php echo htmlspecialchars(($_ENV['IDP_SSO_URL'] ?? getenv('IDP_SSO_URL')) ? 'DTU ADFS Configured' : 'Not configured'); ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function showWelcomePage() {
    $username = $_SESSION['username'] ?? 'Unknown User';
    $authMethod = $_SESSION['auth_method'] ?? 'Unknown';
    $loginTime = $_SESSION['login_time'] ?? 'Unknown';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hello World - Welcome!</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .welcome-container {
                background: white;
                padding: 3rem;
                border-radius: 15px;
                box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 500px;
                width: 90%;
            }
            h1 {
                color: #333;
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
            .username {
                color: #667eea;
                font-weight: bold;
                font-size: 1.2rem;
            }
            .auth-info {
                background: #f8f9fa;
                padding: 1.5rem;
                border-radius: 10px;
                margin: 2rem 0;
                text-align: left;
            }
            .auth-info h3 {
                margin-top: 0;
                color: #495057;
            }
            .auth-info p {
                margin: 0.5rem 0;
                color: #6c757d;
            }
            .logout-btn {
                background: #dc3545;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: transform 0.2s, box-shadow 0.2s;
                margin-top: 1rem;
            }
            .logout-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                background: #c82333;
            }
            .celebration {
                font-size: 3rem;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="welcome-container">
            <div class="celebration">🎉</div>
            <h1>Hello World!</h1>
            <p>Welcome, <span class="username"><?php echo htmlspecialchars($username); ?></span>!</p>
            
            <div class="auth-info">
                <h3>📋 Session Information</h3>
                <p><strong>User:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p><strong>Authentication Method:</strong> <?php echo htmlspecialchars($authMethod); ?></p>
                <p><strong>Login Time:</strong> <?php echo htmlspecialchars($loginTime); ?></p>
                <p><strong>Session ID:</strong> <?php echo htmlspecialchars(substr(session_id(), 0, 8)) . '...'; ?></p>
            </div>
            
            <p>🚀 You have successfully authenticated and can now access protected content!</p>
            
            <a href="?logout=1" class="logout-btn">
                🚪 Logout
            </a>
        </div>
    </body>
    </html>
    <?php
}
