# automatic-waffle

This repository contains a minimal Laravel-ready development container.

Open the folder in VS Code with the Dev Containers extension to start
a container with PHP 8.3, Composer and Node.js preinstalled.

On startup the container runs `.devcontainer/postStartCommand.sh` which installs
Composer and npm dependencies when `composer.json` or `package.json` are
present. If those files do not exist the commands are skipped.

See `.devcontainer/DEVELOPMENT_GUIDE.md` for basic usage.

## Hello CAS World

1. Copy `.env.example` to `.env` and update the CAS variables for your server.
2. Install dependencies:
   ```bash
   composer install
   ```
3. (Optional) Verify your configuration by running the test script:
   ```bash
   php test-cas.php
   ```
The script prints the CAS login URL if everything is configured correctly. It
   will warn if `.env` is missing or CAS variables are not set.

4. Start the built-in PHP server:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
5. Open `http://localhost:8000` in your browser. After CAS authentication you
   should see a greeting with your username.

## SAML Setup

If your identity provider offers SAML 2.0 (for example ADFS), download its
`federationmetadata.xml` file (commonly found under a
`/federationmetadata/2007-06/` path) and extract the certificate and
endpoints. The file `SAML_SETUP.md` contains example environment variables and
explains how to obtain values from the metadata.

## DTU Example Configuration

The repository includes defaults for DTU's Single Sign-On setup. Copy `.env.example` to `.env`
and the values will point to:

- **CAS**: `sso.dtu.dk`
- **SAML/ADFS**: `sts.ait.dtu.dk`

With these settings the included `test-cas.php` and `test-saml.php` scripts
should print DTU login URLs when executed.

If the scripts report that the server cannot be reached, your environment may
block outbound connections to the DTU authentication servers. The test scripts
now attempt to fetch the login pages and will display a clear error when a
network issue occurs.
