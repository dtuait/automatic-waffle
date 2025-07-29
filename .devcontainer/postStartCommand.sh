#!/bin/bash
set -e

composer install --no-interaction || true
npm install || true
cp .env.example .env 2>/dev/null || true
