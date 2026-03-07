# Architecture

The idaé API project is built as a lightweight PHP REST framework backed by MongoDB, with supplementary Node.js helpers.

## Core Components

- **Entrypoint**: `web/index.php` bootstraps requests. All routing is handled by `web/bin/classes/ClassRouter.php` (AltoRouter).
- **API Layer**: Requests flow through `IdaeApiRest` → `IdaeApiParser` → `IdaeQuery`. These classes live under `web/bin/engine/Idae` and enforce the JSON-based IDQL and generic REST conventions.
- **Data Store**: MongoDB is accessed using the `mongodb/mongodb` PHP driver (declared in `web/bin/composer.json`). Queries are centralized in `IdaeQuery`.
- **Node Helpers**: A small Node process (under `web/bin/node/`) provides socket/cron functionality. It can be started via `auto_start.sh`/`auto_start.bat`.
- **Configuration**: `web/conf.inc.php` loads environment variables, autoloaders, and sets CORS/headers. Constants such as `MDB_HOST` appear in `constants.php`.

## Development & Testing

- PHP dependencies are managed with Composer (`web/bin/composer.json`).
- PHPUnit tests reside in `web/bin/tests` and can be run via `./vendor/bin/phpunit` or through Docker Compose.
- Docker support is defined in `docker-compose.yml` and `Dockerfile` at the repo root.

## Deployment & Runtime

- Designed to run behind Apache/nginx with `web/` as the document root.
- The router dispatches `/api/idql/*` and `/api/*` requests to the API layer.
- Node socket service is optional but started automatically in local development.

This document will be expanded as the architecture evolves, but provides the current high‑level overview.
