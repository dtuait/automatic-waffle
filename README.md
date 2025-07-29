# automatic-waffle

This repository contains a minimal Laravel-ready development container.

Open the folder in VS Code with the Dev Containers extension to start
a container with PHP 8.3, Composer and Node.js preinstalled.

See `.devcontainer/DEVELOPMENT_GUIDE.md` for basic usage.

## Hello CAS World

1. Copy `.env.example` to `.env` and update the CAS variables for your server.
2. Install dependencies:
   ```bash
   composer install
   ```
3. Start the built-in PHP server:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
4. Open `http://localhost:8000` in your browser. After CAS authentication you
   should see a greeting with your username.
