<?php

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost:8000';

$settings = array(
    'sp' => array(
        'entityId' => $_ENV['SP_ENTITY_ID'] ?? $baseUrl . '/metadata',
        'assertionConsumerService' => array(
            'url' => $_ENV['SP_ACS_URL'] ?? $baseUrl . '/saml/acs',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        'singleLogoutService' => array(
            'url' => $baseUrl . '/saml/sls',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        'x509cert' => '',
        'privateKey' => '',
        'x509certNew' => '',
    ),
    'idp' => array(
        'entityId' => $_ENV['IDP_ENTITY_ID'] ?? 'https://sts.ait.dtu.dk/adfs/services/trust',
        'singleSignOnService' => array(
            'url' => $_ENV['IDP_SSO_URL'] ?? 'https://sts.ait.dtu.dk/adfs/ls/',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'singleLogoutService' => array(
            'url' => $_ENV['IDP_SLO_URL'] ?? 'https://sts.ait.dtu.dk/adfs/ls/?wa=wsignout1.0',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'x509cert' => '',
    ),
);

// Advanced settings
$advancedSettings = array(
    'security' => array(
        'nameIdEncrypted' => false,
        'authnRequestsSigned' => filter_var($_ENV['AUTHN_REQUESTS_SIGNED'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'logoutRequestSigned' => false,
        'logoutResponseSigned' => false,
        'signMetadata' => false,
        'wantAssertionsSigned' => filter_var($_ENV['WANT_ASSERTIONS_SIGNED'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'wantNameId' => true,
        'wantNameIdEncrypted' => false,
        'wantAssertionsEncrypted' => false,
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
        'lowercaseUrlencoding' => false,
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
