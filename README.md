# idae.api

This repository contains the idae.api PHP project — a lightweight REST / IDQL API backed by MongoDB, designed to run under the `web/` document root. The codebase is organized around a router → API parser → query layer pattern and includes Docker helper scripts and test infrastructure (PHPUnit + Docker Compose).

**Quick links**

- Entrypoint: [web/index.php](web/index.php)
- Router: [web/bin/classes/ClassRouter.php](web/bin/classes/ClassRouter.php)
- API dispatcher: [web/bin/engine/Idae/Api/IdaeApiRest.php](web/bin/engine/Idae/Api/IdaeApiRest.php)
- Query layer: [web/bin/engine/Idae/Query/IdaeQuery.php](web/bin/engine/Idae/Query/IdaeQuery.php)
- Tests: [web/bin/tests](web/bin/tests)
- Docker compose: [docker-compose.yml](docker-compose.yml)

## Overview

The API exposes two main types of endpoints:

- `/api/idql/<scheme>` — accepts an IDQL JSON payload describing DB operations (find, group, distinct, parallel, etc.).
- `/api/*` — generic REST paths routed to `IdaeApiRest::doRest()`; query params and JSON body are used depending on the HTTP method.

The project uses MongoDB as the datastore (via the `mongodb/mongodb` library and `ext-mongodb`) and contains a small Node helper process under `web/bin/node` for sockets/crons.

## Example: IDQL request (curl)

POST /api/idql/products

Request body (JSON):

```json
{
  "method": "find",
  "scheme": "products",
  "limit": 10,
  "page": 0,
  "where": {"status": "active"}
}
```

Example curl call:

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"method":"find","scheme":"products","limit":10,"where":{"status":"active"}}' \
  http://localhost:8081/api/idql/products
```

Response format is JSON and depends on the parser `output_method` (commonly `raw` or `raw_casted`). See `IdaeApiRest` for envelope details.

## Example: Generic REST request (curl)

Any method to `/api/<...>` is routed into the REST handler. Example GET:

```bash
curl "http://localhost:8081/api/products?limit=10&status=active"
```

POST example with body:

```bash
curl -X POST http://localhost:8081/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"New product","price":12.5}'
```

The REST handler maps the input into `IdaeQuery` calls according to the scheme configuration.

## Running locally with Docker

The repo includes a `docker-compose.yml` describing two services: `app` (PHP + Apache) and `mongo` (MongoDB). By default the compose file mounts an init script that creates a test DB and test user for integration tests.

Start services:

```bash
docker compose up -d --build
```

To use a host-installed MongoDB instead of the included `mongo` service, set the `MDB_HOST` environment variable to `host.docker.internal` (Windows) or your host IP. Example:

```bash
docker compose up -d --build
docker compose exec app bash -lc "export MDB_HOST=host.docker.internal && composer install"
```

There is an override `docker-compose.override.yml` provided to run Mongo with `--noauth` for local development. That override intentionally avoids mounting the init script.

## Tests (unit + integration)

Project tests live under `web/bin/tests`. PHPUnit configuration: `web/bin/phpunit.xml`.

Unit tests run quickly inside the `app` container:

```bash
docker compose exec app bash -lc "cd /var/www/html/web/bin && ./vendor/bin/phpunit --testsuite unit -c phpunit.xml"
```

Integration tests require a Mongo instance reachable from the `app` container. Two options:

- Use the included `mongo` service and the default init script (creates `idae_test` DB and `idae_test_user`).
- Use a host Mongo and set `MDB_HOST=host.docker.internal`; a fixture loader is provided to insert test data into the target DB.

Fixture loader (idempotent): `web/bin/tests/fixtures/fixture_loader.php`

Run it inside the `app` container (example using host-mapped Mongo):

```bash
docker compose exec app bash -lc "cd /var/www/html/web/bin && MDB_HOST=host.docker.internal MDB_USER=admin MDB_PASS=gwetme2011 php tests/fixtures/fixture_loader.php"
```

Then run integration tests:

```bash
docker compose exec app bash -lc "cd /var/www/html/web/bin && MDB_HOST=host.docker.internal MDB_USER=admin MDB_PASS=gwetme2011 php vendor/phpunit/phpunit/phpunit --testsuite integration -c phpunit.xml"
```

Notes: some environments require `ext-mongodb` / libmongoc built with SSL to use SCRAM authentication. For local dev we provide a no-auth override and the fixture loader to keep tests reproducible.

## Configuration & environment variables

The application reads its runtime constants from `web/bin/config/constants.php`. Important env vars you may set in Docker or your environment:

- `MDB_HOST` — Mongo host (default `mongo` in compose). Use `host.docker.internal` to connect to host Mongo from container.
- `MDB_USER`, `MDB_PASSWORD` — Mongo admin/user credentials.
- `MDB_PREFIX` — optional DB prefix used by the app.

You can provide overrides by exporting environment variables before starting containers or by editing `web/bin/config/env_constants.php` which is included when present.

## Code pointers and conventions

- Database access is centralized in `web/bin/engine/Idae/Query/IdaeQuery.php`.
- The connection singleton is implemented in `web/bin/engine/Idae/Connect/IdaeConnect.php`.
- API parsing and routing are in `web/bin/engine/Idae/Api`.
- Templates and view fragments live under `web/bin/templates`.

If you add new schemes or change the API parsing behavior, follow existing patterns (IdaeApiParser → IdaeApiRest → IdaeQuery) to keep behavior consistent.

## Contributing & running the dev workflow

1. Clone repository and open at project root.
2. Build and run containers with Docker Compose.
3. Run `composer install` in the container or locally (project vendors are installed by the build step in Dockerfile by default).
4. Run unit tests, then integration tests with the fixture loader if using a host Mongo.

Suggested commands (copy/paste):

```bash
# Build and start
docker compose up -d --build

# Install dependencies inside container (if needed)
docker compose exec app bash -lc "cd /var/www/html/web/bin && composer install --no-interaction"

# Load fixtures (host Mongo example)
docker compose exec app bash -lc "cd /var/www/html/web/bin && MDB_HOST=host.docker.internal MDB_USER=admin MDB_PASS=gwetme2011 php tests/fixtures/fixture_loader.php"

# Run tests
docker compose exec app bash -lc "cd /var/www/html/web/bin && php vendor/phpunit/phpunit/phpunit --testsuite unit -c phpunit.xml"
docker compose exec app bash -lc "cd /var/www/html/web/bin && MDB_HOST=host.docker.internal MDB_USER=admin MDB_PASS=gwetme2011 php vendor/phpunit/phpunit/phpunit --testsuite integration -c phpunit.xml"
```

## Troubleshooting

- If integration tests fail with authentication errors mentioning SCRAM_SHA and libmongoc SSL, the PHP container's Mongo driver has been built without SSL/SASL support. Fix options:
  - Use host Mongo (host.docker.internal) and admin creds (quick workaround).
  - Rebuild the PHP image ensuring `libssl-dev` and SASL build dependencies are present and `pecl install mongodb` picks up SSL (longer).

- If containers cannot resolve the `mongo` service name, ensure you run `docker compose` from the repository root and that the network is up (`docker compose up -d`).

## Next steps and ideas

- Add a small `Makefile` or Composer script to run `fixture_loader.php` automatically before integration tests.
- Add a GitHub Actions workflow that builds the PHP image, starts services, runs the fixture loader, and executes both test suites.

---

If you want, I can:

- Add a `Makefile` / Composer script to run fixtures + tests.
- Add a GitHub Actions CI config that executes the same Docker Compose setup and tests.

Tell me which of those you'd like next and I'll implement it.
