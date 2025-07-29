<?php
require __DIR__ . '/vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}


echo "Testing CAS Authentication...\n";

try {
    $host = $_ENV['CAS_HOST'] ?? getenv('CAS_HOST');
    if (!$host) {
        throw new Exception('CAS_HOST not configured');
    }
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

    echo "✓ Client configured\n";
    $loginUrl = phpCAS::getServerLoginURL();
    echo "✓ Login URL: " . $loginUrl . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

