<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', '1');

// Text to print during the test
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

    echo $output;

    echo "✓ Client configured\n";
    $loginUrl = "https://{$host}";
    if ((int)$port !== 443) {
        $loginUrl .= ":{$port}";
    }
    $loginUrl .= rtrim($context, '/') . '/login?service=' . urlencode($baseUrl);
    echo "✓ Login URL: " . $loginUrl . "\n";

    echo "Checking CAS server response...\n";

    $caOptions = [];
    if ($caCert && file_exists($caCert)) {
        $caOptions['cafile'] = $caCert;
    }
    $contextOptions = [
        'http' => ['method' => 'GET'],
        'ssl'  => array_merge(['verify_peer' => (bool)$caCert,
                               'verify_peer_name' => (bool)$caCert], $caOptions),
    ];
    $ctx = stream_context_create($contextOptions);
    $html = @file_get_contents($loginUrl, false, $ctx);
    if ($html === false) {
        $err = error_get_last();
        echo "✗ Failed to fetch CAS login page: " . ($err['message'] ?? 'unknown error') . "\n";
    } else {
        if (stripos($html, 'CAS') === false && stripos($html, '<html') === false) {
            echo "✗ Unexpected response from CAS server\n";
        } else {
            echo "✓ CAS login page loaded successfully\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

