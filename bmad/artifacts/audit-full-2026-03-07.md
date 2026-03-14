# Audit — Full — 2026-03-07

Summary

- Project: idae.api (PHP + MongoDB)
- Entrypoints: web/index.php → web/bin/classes/ClassRouter.php → web/bin/engine/Idae/Api/IdaeApiRest.php → web/bin/engine/Idae/Query/IdaeQuery.php
- Tests: web/bin/tests (phpunit) — unit + integration (fixture loader provided)
- Docker: docker-compose.yml defines 'app' (PHP/Apache) and 'mongo' services; an init script and default credentials are present for local testing.

Scope & files inspected

- web/bin/composer.json (dependencies & php platform)
- docker-compose.yml
- web/index.php
- web/bin/classes/ClassRouter.php
- web/bin/engine/Idae/Api/IdaeApiRest.php
- web/bin/engine/Idae/Query/IdaeQuery.php
- web/bin/phpunit.xml and tests folder

Key Findings

1) Stack & dependencies
- Composer lists PHP platform 7.4.33 and requires: mongodb/mongodb (1.6.x-dev), alcaeus/mongo-php-adapter, mustache, altorouter, nayjest/str-case-converter, phpunit ^8.4, predis/predis.
- Using a dev-version (1.6.x-dev) for mongodb/mongodb is risky; prefer a stable release pinned with semantic versioning.

2) Entrypoints & routing
- Routing is clear and minimal: AltoRouter in ClassRouter registers /api/idql/[scheme] and /api/[*]. The router delegates to IdaeApiRest which parses and dispatches IDQL and generic REST requests.

3) Query layer and DB access
- IdaeQuery centralizes MongoDB access but often materializes entire cursors with iterator_to_array(), and does nested findOne() per-row lookups. This may cause high memory usage and slow N+1 database patterns on large datasets.
- Several methods toggle global ini settings (mongo.long_as_object), and debug flags (ini_set('display_errors',55)) are present; those should be removed/controlled per-environment.

4) Tests & CI readiness
- PHPUnit configuration (phpunit.xml) declares both unit and integration suites. Integration tests require a running MongoDB and a fixture loader (web/bin/tests/fixtures/fixture_loader.php).
- No CI pipeline detected in repository; recommend adding GitHub Actions (or similar) to run unit tests and optionally integration tests with a Mongo service.

5) Docker & secrets
- docker-compose.yml contains plain text defaults for MDB_USER/MDB_PASSWORD and MONGO_INITDB_ROOT_PASSWORD (used for local integration tests). Committed credentials should be considered test-only and rotated; replace with environment variables or Docker secrets in shared repos.

6) Security & input handling
- IdaeApiRest::getJson() handles JSON content types but returns an empty array on invalid JSON after calling json_response(400, 'Invalid JSON!'). Consider explicit exit or throwing an exception so invalid payloads do not continue processing.
- CORS appears permissive (project notes indicate Access-Control-Allow-Origin: *) — acceptable for local dev but review for production.

7) Performance
- Aggressive use of iterator_to_array() and returning full documents can lead to high memory consumption; prefer streaming or pagination for large result sets.
- 'parallel' queries are executed sequentially in code (recursive doQuery calls) — name may be misleading; consider real parallelization only where safe.

8) Maintainability & code hygiene
- Several debug statements, var_dump/die lines, and magic ini toggles are present. Add logging instead of echo/var_dump and ensure production config disables debug.
- Naming and file organization follow a consistent router→API→query pattern which is good; conform to PSR-12 whitespace/style for new code.

Recommendations (prioritized)

1. Stability & dependencies
   - Replace dev-version mongodb/mongodb with a stable release (use the version compatible with your PHP target). Run `composer update` and test.
   - Upgrade PHP to a supported LTS (8.1 or 8.2), update Dockerfile and CI accordingly.

2. CI / Tests
   - Add a GitHub Actions workflow to run `composer install` and unit tests; optionally run integration tests in a job that brings up MongoDB and runs fixture_loader.php.
   - Add `composer audit` and a security scan step to CI.

3. Code & performance
   - Refactor IdaeQuery::find and related methods to avoid iterator_to_array() where possible; implement streaming, cursor batching, or paginate by default.
   - Replace per-row findOne joins with aggregation $lookup or batched prefetch to avoid N+1 queries.
   - Remove debug ini_set/display_errors lines; centralize environment-controlled logging.

4. Security & config
   - Remove plaintext credentials from source; use environment variables, .env files excluded from VCS, or Docker secrets for production.
   - Harden CORS and response headers for production.
   - Validate and sanitize all incoming payloads strictly (IdaeApiParser/IdaeApiRest layer).

5. Operational
   - Rebuild Docker image ensuring ext-mongodb is compiled with SSL if SCRAM authentication is used by CI/production.
   - Add a Makefile or composer scripts to run fixture loader and tests easily.

Actionable next steps

- Run: `cd web/bin && composer install` and run unit tests: `./vendor/bin/phpunit --testsuite unit`.
- Implement GH Actions: `php composer install` + unit test job; add optional integration job with mongo service and fixture loader.
- Create developer stories for: (S1) Refactor query memory usage, (S2) Rebuild Docker / upgrade PHP + drivers, (S3) Add CI + security scanning, (S4) Remove debug and stabilize logging.

Appendix — files discovered

- docker-compose.yml
- web/index.php
- web/bin/classes/ClassRouter.php
- web/bin/engine/Idae/Api/IdaeApiRest.php
- web/bin/engine/Idae/Query/IdaeQuery.php
- web/bin/composer.json
- web/bin/phpunit.xml
- web/bin/tests/

Commands

- Local dev:
  - `docker compose up -d --build`
  - `cd web/bin && composer install`
  - `cd web/bin && ./vendor/bin/phpunit --testsuite unit`

- Recommended CI snippet (GitHub Actions):
  - job: composer install + run phpunit

---

Generated by bmad-master audit on 2026-03-07.
