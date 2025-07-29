<?php
require __DIR__ . '/vendor/autoload.php';
ob_start();
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');

// Collect messages without printing them until phpCAS has initialized
$output = '';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} else {
    $output .= "Warning: .env file not found. Copy .env.example to .env and update the CAS variables.\n";
}

// Append initial status text
$output .= "Testing CAS Authentication...\n";

try {
    $host = $_ENV['CAS_HOST'] ?? getenv('CAS_HOST') ?: 'cas.example.com';
    if (!$host) {
        throw new Exception('CAS_HOST not configured. Did you copy .env.example to .env?');
    }
    $port = $_ENV['CAS_PORT'] ?? getenv('CAS_PORT') ?: 443;
    $context = $_ENV['CAS_CONTEXT'] ?? getenv('CAS_CONTEXT') ?: '/cas';
    $caCert = $_ENV['CAS_CA_CERT'] ?? getenv('CAS_CA_CERT');
    $baseUrl = $_ENV['SERVICE_BASE_URL'] ?? getenv('SERVICE_BASE_URL') ?: 'http://localhost';

    phpCAS::client(CAS_VERSION_2_0, $host, (int)$port, $context, $baseUrl);
    if ($caCert) {
        phpCAS::setCasServerCACert($caCert);
    } else {
        phpCAS::setNoCasServerValidation();
    }

    ob_end_clean();
    // Now that phpCAS is initialized, print any queued output
    echo $output;

    echo "✓ Client configured\n";
    $loginUrl = "https://{$host}";
    if ((int)$port !== 443) {
        $loginUrl .= ":{$port}";
    }
    $loginUrl .= rtrim($context, '/') . '/login?service=' . urlencode($baseUrl);
    echo "✓ Login URL: " . $loginUrl . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
ob_end_flush();

