<?php

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

use OneLogin\Saml2\IdPMetadataParser;

/**
 * Expand environment variable placeholders like ${VAR} within a value.
 */
function expandEnv($value)
{
    return preg_replace_callback('/\${([A-Z0-9_]+)}/i', function ($m) {
        return $_ENV[$m[1]] ?? getenv($m[1]) ?? $m[0];
    }, $value);
}

/**
 * Retrieve a URL from the environment and validate it.
 */
function envUrl($key, $default)
{
    $value = expandEnv($_ENV[$key] ?? getenv($key) ?? '');
    if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
        return $value;
    }
    return $default;
}

$baseUrl = envUrl('BASE_URL', 'http://localhost:8000');

// Option 1: Use metadata file to automatically configure IDP settings
$idpSettings = array();
$metadataFile = __DIR__ . '/../federationmetadata.xml';

if (file_exists($metadataFile)) {
    try {
        $metadataContent = file_get_contents($metadataFile);
        $parsedMetadata = IdPMetadataParser::parseXML($metadataContent);

        // Extract just the IdP portion of the metadata
        $idpSettings = $parsedMetadata['idp'];

        // Override with any environment variables if they exist
        if (isset($_ENV['IDP_ENTITY_ID'])) {
            $idpSettings['entityId'] = envUrl('IDP_ENTITY_ID', $idpSettings['entityId']);
        }
        if (isset($_ENV['IDP_SSO_URL'])) {
            $idpSettings['singleSignOnService']['url'] = envUrl('IDP_SSO_URL', $idpSettings['singleSignOnService']['url']);
        }
        
    } catch (Exception $e) {
        // Fallback to manual configuration if metadata parsing fails
        $idpSettings = array(
            'entityId' => envUrl('IDP_ENTITY_ID', 'http://sts.ait.dtu.dk/adfs/services/trust'),
            'singleSignOnService' => array(
                'url' => envUrl('IDP_SSO_URL', 'https://sts.ait.dtu.dk/adfs/ls/'),
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ),
            'singleLogoutService' => array(
                'url' => envUrl('IDP_SLO_URL', 'https://sts.ait.dtu.dk/adfs/ls/'),
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ),
            'x509cert' => 'MIIC2DCCAcCgAwIBAgIQY7LG6hqng7JIcPPejLlT+zANBgkqhkiG9w0BAQsFADAoMSYwJAYDVQQDEx1BREZTIFNpZ25pbmcgLSBzdHMuYWl0LmR0dS5kazAeFw0xOTA4MjkxMjIyMDNaFw0yOTA4MjYxMjIyMDNaMCgxJjAkBgNVBAMTHUFERlMgU2lnbmluZyAtIHN0cy5haXQuZHR1LmRrMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArKhryBSorco12BCTzSMj6mOqxeHuSf1NrqsCLPoEoSGlLXI1EJrNg5tR9oOhpMCxmkc7ZtYkklLDErdgKgmr+uAwGUt+7WbU7OoUsoJhN2UwXHTBBbzYo13bk0+QUzO3ejh/dTIBSLXDJHj5gj5EIbONBR7YMZmU2skSJzi+z88tKyG14sHEtFZgyxDOImwl56uh8PGmQ8tOr8Rj4NU0g5mknBt1gqjoYJd20KziFubqHm/Kua2b2Ix5TMBYnOSDq0f2kkPWHVFACxFCkEy6yey0n9+vdbbdUaQP5p+0IJvLiJ4BzWYO1U3eiLRe9Rz4YUe2xlfTu8kIA3ZCy3pdeQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQB9Xx059t9aOFg5zxCVKtgSI77JDqTfrU1Zlr3uxCs6tVkYMA5DGaPRaaLa6Gui1X3+LQJzDZyVj6MsUNwZwxWJ2Y/mLI3zJGcLW3xP5unm57/PjU3KNuORjE6RfMFjoHEZHHhOUUP+kEUGLSiYKKSJHvHXzUFZP+g6YVjfcdXlN+3H0YnZadLkC4Ur2T8FXy/VCp5QVLhRjNY1Fe8cvXrIGFQG6d1vn3PHCZxtTjRb6+dKHRqHtxyku/1OZ+F6otWzJpSBWTdmNzzMyeqpdJGJLLaoR6AmLYGbXW96ylJUmi24r24Xtt+Pm5zASHOV9aiAVNUQ+o9u/a2oC841MsJI',
        );
    }
} else {
    // Fallback if no metadata file exists
    $idpSettings = array(
        'entityId' => envUrl('IDP_ENTITY_ID', 'http://sts.ait.dtu.dk/adfs/services/trust'),
        'singleSignOnService' => array(
            'url' => envUrl('IDP_SSO_URL', 'https://sts.ait.dtu.dk/adfs/ls/'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'singleLogoutService' => array(
            'url' => envUrl('IDP_SLO_URL', 'https://sts.ait.dtu.dk/adfs/ls/'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'x509cert' => 'MIIC2DCCAcCgAwIBAgIQY7LG6hqng7JIcPPejLlT+zANBgkqhkiG9w0BAQsFADAoMSYwJAYDVQQDEx1BREZTIFNpZ25pbmcgLSBzdHMuYWl0LmR0dS5kazAeFw0xOTA4MjkxMjIyMDNaFw0yOTA4MjYxMjIyMDNaMCgxJjAkBgNVBAMTHUFERlMgU2lnbmluZyAtIHN0cy5haXQuZHR1LmRrMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArKhryBSorco12BCTzSMj6mOqxeHuSf1NrqsCLPoEoSGlLXI1EJrNg5tR9oOhpMCxmkc7ZtYkklLDErdgKgmr+uAwGUt+7WbU7OoUsoJhN2UwXHTBBbzYo13bk0+QUzO3ejh/dTIBSLXDJHj5gj5EIbONBR7YMZmU2skSJzi+z88tKyG14sHEtFZgyxDOImwl56uh8PGmQ8tOr8Rj4NU0g5mknBt1gqjoYJd20KziFubqHm/Kua2b2Ix5TMBYnOSDq0f2kkPWHVFACxFCkEy6yey0n9+vdbbdUaQP5p+0IJvLiJ4BzWYO1U3eiLRe9Rz4YUe2xlfTu8kIA3ZCy3pdeQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQB9Xx059t9aOFg5zxCVKtgSI77JDqTfrU1Zlr3uxCs6tVkYMA5DGaPRaaLa6Gui1X3+LQJzDZyVj6MsUNwZwxWJ2Y/mLI3zJGcLW3xP5unm57/PjU3KNuORjE6RfMFjoHEZHHhOUUP+kEUGLSiYKKSJHvHXzUFZP+g6YVjfcdXlN+3H0YnZadLkC4Ur2T8FXy/VCp5QVLhRjNY1Fe8cvXrIGFQG6d1vn3PHCZxtTjRb6+dKHRqHtxyku/1OZ+F6otWzJpSBWTdmNzzMyeqpdJGJLLaoR6AmLYGbXW96ylJUmi24r24Xtt+Pm5zASHOV9aiAVNUQ+o9u/a2oC841MsJI',
    );
}

$settings = array(
    'sp' => array(
        'entityId' => envUrl('SP_ENTITY_ID', $baseUrl . '/saml-metadata.php'),
        'assertionConsumerService' => array(
            'url' => envUrl('SP_ACS_URL', $baseUrl . '/saml-acs.php'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        'singleLogoutService' => array(
            'url' => envUrl('SP_SLS_URL', $baseUrl . '/saml/sls'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
        'x509cert' => '',
        'privateKey' => '',
        'x509certNew' => '',
    ),
    'idp' => $idpSettings,
);

// Advanced settings - disable certificate requirements for testing
$advancedSettings = array(
    'security' => array(
        'nameIdEncrypted' => false,
        'authnRequestsSigned' => false, // Disabled for testing
        'logoutRequestSigned' => false,
        'logoutResponseSigned' => false,
        'signMetadata' => false,
        'wantAssertionsSigned' => false, // Disabled for testing
        'wantNameId' => true,
        'wantNameIdEncrypted' => false,
        'wantAssertionsEncrypted' => false,
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
        'lowercaseUrlencoding' => false,
        'relaxDestinationValidation' => true,
        'destinationStrictlyMatches' => false,
        'rejectUnsolicitedResponsesWithInResponseTo' => false,
    ),
    'contactPerson' => array(
        'technical' => array(
            'givenName' => 'Tech Support',
            'emailAddress' => 'tech@example.com'
        ),
        'support' => array(
            'givenName' => 'Support',
            'emailAddress' => 'support@example.com'
        ),
    ),
    'organization' => array(
        'en-US' => array(
            'name' => 'Your Organization',
            'displayname' => 'Your Organization',
            'url' => $baseUrl
        ),
    ),
);

return array_merge_recursive($settings, $advancedSettings);
