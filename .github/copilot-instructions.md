# Copilot / AI agent instructions for idae.api

This file provides concise, repository-specific guidance so an AI coding agent can be immediately productive.

**Big Picture:**
- **API core:** PHP application under the `web/` folder. Entry points are `web/index.php` and the router in [web/bin/classes/ClassRouter.php](web/bin/classes/ClassRouter.php#L1-L120). Requests under `/api/` are dispatched to the API layer.
- **Routing → API → Query:** Requests go through `Router` → `IdaeApiRest` ([web/bin/engine/Idae/Api/IdaeApiRest.php](web/bin/engine/Idae/Api/IdaeApiRest.php#L1-L220)) → `IdaeQuery` (data layer). The API supports two main endpoints: `/api/idql/[scheme]` (idql JSON payloads) and `/api/*` (generic REST handlers).
- **Data store:** MongoDB via the `mongodb/mongodb` PHP driver (see `web/bin/composer.json`). `IdaeQuery` performs collection operations (find, group, distinct, parallel).
- **Node helpers:** A small Node process for sockets/crons lives under `web/bin/node/` (e.g. `app_https_main.js`). It is started by the project's start scripts.

**Entrypoints & important files:**
- `web/index.php` — bootstrap for HTTP requests.
- [web/bin/classes/ClassRouter.php](web/bin/classes/ClassRouter.php#L1-L120) — route definitions, shows `/api/idql/[*:scheme]` and `/api/[*:uri_vars]` handlers.
- [web/bin/engine/Idae/Api/IdaeApiRest.php](web/bin/engine/Idae/Api/IdaeApiRest.php#L1-L220) — main API parser/dispatcher and JSON output formats (`raw`, `raw_casted`, `html`). Look here for accepted query shapes and response format.
- `web/conf.inc.php` — environment setup, `include_once('bin/vendor/autoload.php')`, XDebug and HTTP headers; important for running locally.
- `web/bin/composer.json` — PHP dependencies and dev tools (phpunit).
- `web/bin/node/` — Node socket & cron helpers (started by `auto_start.sh` / `auto_start.bat`).

**Request patterns & examples**
- Idql POST example (route defined in `ClassRouter`):

  POST /api/idql/products
  Body (application/json):
  {
    "method": "find",
    "scheme": "products",
    "limit": 10,
    "page": 0,
    "where": {"status": "active"}
  }

- Generic REST example: any method to `/api/<...>` is routed to `IdaeApiRest::doRest()`; query params are read from `$_GET` or JSON body depending on method.

**Project conventions & gotchas**
- Environment bootstrap expects `$_SERVER['CONF_INC']` or to include `web/conf.inc.php` from entrypoints. When running tests or CLI, ensure `CONF_INC` is set or include the file manually.
- Autoload uses `web/bin/vendor/autoload.php` (run `composer install` in `web/bin`).
- Many internal classes are namespaced under `Idae\\` or live inside `web/bin/engine/Idae` — prefer following existing namespacing and class-factory patterns.
- Query format: `IdaeApiRest` uses a parsed array with keys like `scheme`, `method`, `limit`, `page`, `where`, `proj`, `parallel`. Inspect `IdaeApiRest::doQuery()` for behaviors like `parallel` and `distinct` branches.
- Output modes: `raw` (JSON envelope), `raw_casted` (casts using scheme fields), `html` — code paths depend on `output_method` parsed by `IdaeApiParser`.

**Developer workflow & commands**
- Install PHP deps: `cd web/bin && composer install` (creates `web/bin/vendor`).
- Run tests: `cd web/bin && ./vendor/bin/phpunit` (or platform-appropriate vendor binary).
- Start node socket (Linux): `./auto_start.sh` uses `forever` to run `web/bin/node/app_https/app_https_main.js`. On Windows run node directly for quick tests: `node web/bin/node/app_https/app_https_main.js`.
- Web server: project is designed to run behind Apache/nginx with document root at `web/`; ensure `web/conf.inc.php` is reachable and `bin/vendor/autoload.php` exists.

**Where to make changes**
- Add HTTP endpoints: modify `ClassRouter::routes()` to add AltoRouter routes and point to a class#method or a callable.
- Add business logic: implement/extend under `web/bin/engine/Idae` or `web/bin/classes/` following current namespace patterns.

**What to watch for when editing**
- Avoid changing the request parsing and output conventions unless you update `IdaeApiParser` and `IdaeApiRest` together.
- Many files assume `json_encode(..., JSON_PRETTY_PRINT)` responses and set headers in `IdaeApiRest::json_response()`; keep compatible responses.

If anything above is unclear or you want more detail about a specific area (database mapping, `IdaeQuery` internals, or Node socket), tell me which part and I will expand or add short code examples.
