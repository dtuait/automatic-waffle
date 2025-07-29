# automatic-waffle

This repository contains a minimal Laravel-ready development container.

Open the folder in VS Code with the Dev Containers extension to start
a container with PHP 8.3, Composer and Node.js preinstalled.

On startup the container runs `.devcontainer/postStartCommand.sh` which installs
Composer and npm dependencies when `composer.json` or `package.json` are
present. If those files do not exist the commands are skipped.

See `.devcontainer/DEVELOPMENT_GUIDE.md` for basic usage.
