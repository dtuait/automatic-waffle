<?php
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env if using vlucas/phpdotenv
if (file_exists(__DIR__ . '/../.env')) {
    // this will load variables into getenv()
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$host = getenv('CAS_HOST') ?: 'localhost';
$port = getenv('CAS_PORT') ?: 443;
$context = getenv('CAS_CONTEXT') ?: '/cas';
$caCert = getenv('CAS_CA_CERT');

// Service base URL parameter introduced in phpCAS 1.6.0
$baseUrl = getenv('SERVICE_BASE_URL') ?: '';
phpCAS::client(CAS_VERSION_2_0, $host, (int)$port, $context, $baseUrl);
if ($caCert) {
    phpCAS::setCasServerCACert($caCert);
} else {
    phpCAS::setNoCasServerValidation();
}

phpCAS::forceAuthentication();

$user = phpCAS::getUser();

echo "<h1>Hello CAS World, $user!</h1>";
