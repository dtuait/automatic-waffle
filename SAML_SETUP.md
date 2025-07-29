# SAML 2.0 Setup (Example)

This project ships with a simple CAS example, but you can also use SAML 2.0
identity providers such as ADFS. The following environment variables illustrate
what a SAML configuration might look like.

```env
# Service Provider (your application)
SP_ENTITY_ID=https://your-app.example.com/metadata
SP_ACS_URL=https://your-app.example.com/assertion-consumer
SP_CERT=/path/to/your_sp_cert.pem
SP_KEY=/path/to/your_sp_key.pem

# Identity Provider (ADFS example)
IDP_ENTITY_ID=https://idp.example.com/adfs/services/trust
IDP_SSO_URL=https://idp.example.com/adfs/ls/
IDP_SLO_URL=https://idp.example.com/adfs/ls/?wa=wsignout1.0
IDP_CERT=/path/to/idp_cert.pem

# Optional security options
WANT_ASSERTIONS_SIGNED=true
AUTHN_REQUESTS_SIGNED=true

# Base URL of this app
BASE_URL=https://your-app.example.com
```

## Using federationmetadata.xml

Download the metadata from your identity provider and extract the certificate
and endpoints:

1. Copy the `X509Certificate` value from the metadata and save it as a PEM file.
   Use this path for `IDP_CERT`.
2. Use the `entityID` attribute for `IDP_ENTITY_ID`.
3. The `SingleSignOnService` `Location` becomes `IDP_SSO_URL`.
4. The optional `SingleLogoutService` `Location` becomes `IDP_SLO_URL`.

These values allow a SAML library (for example, onelogin/php-saml) to
communicate with your identity provider.
