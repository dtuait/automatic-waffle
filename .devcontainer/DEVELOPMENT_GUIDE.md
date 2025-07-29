# Development Guide

1. Start the dev container using VS Code.
2. The container installs PHP, Composer and Node.js.
3. Run Laravel development server:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
4. For Vite hot reload use:
   ```bash
   npm run dev
   ```
5. Database credentials inside the container:
   - Host: `db`
   - User: `laravel`
   - Password: `laravel`
   - Database: `laravel`
