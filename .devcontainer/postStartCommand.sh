#!/bin/bash
set -e

if [ -f composer.json ]; then
    composer install --no-interaction
fi

if [ -f package.json ]; then
    npm install
fi
cp .env.example .env 2>/dev/null || true
