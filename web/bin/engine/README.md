# Idae Engine — overview

This directory contains the core PHP "engine" libraries used by the idae.api application. It is not an application entrypoint — it provides reusable components used by the web/API layer under `web/`.

Key responsibilities
- Database query layer and schema helpers (MongoDB focused).
- API parsing and translation (URI -> internal "idql" query arrays).
- Scheme/field model helpers and small utilities (date, string, url, cast functions).

Major components
- `Idae\Query` — main query façade that applications use to access collections: [web/bin/engine/Idae/Query/IdaeQuery.php](web/bin/engine/Idae/Query/IdaeQuery.php#L1-L20).
- `Idae\Api` — parser, operators and helper clients used by the HTTP API: [web/bin/engine/Idae/Api/IdaeApiParser.php](web/bin/engine/Idae/Api/IdaeApiParser.php#L1-L20), [web/bin/engine/Idae/Api/IdaeApiQuery.php](web/bin/engine/Idae/Api/IdaeApiQuery.php#L1-L40).
- `Idae\App` — application-level helpers to read scheme metadata (fields, lists): [web/bin/engine/Idae/App/IdaeAppBase.php](web/bin/engine/Idae/App/IdaeAppBase.php#L1-L40).
- `Idae\Db` & `Idae\Connect` — low level connection plumbing and legacy DB helpers; prefer `Idae\Query` for reads/writes.
- `Idae\Data\Scheme` — schema models and field drawers used to render and cast fields.

Conventions & patterns
- Collections are addressed by a short code (e.g. `products`). The corresponding id field is `id<code>` (for example `idproducts`) and is used as a stable integer identifier.
- The engine exposes a single query façade: instantiate `new \Idae\Query\IdaeQuery('<scheme>')` or call `collection('<scheme>')` then `find()`, `findOne()`, `group()`, `distinct()`, `insert()`, `update()`.
- API layer accepts an "idql" style array with keys like `scheme`, `where`, `limit`, `page`, `proj`, `parallel`. URI routing to idql is performed by `IdaeApiParser`.
- Auto-increment counters are stored in `sitebase_increment` and updated via `getNext()` helper in the query layer.

Operational notes
- The project requires `web/bin/vendor/autoload.php` (run `composer install` in `web/bin`). See [web/bin/composer.json](web/bin/composer.json#L1-L40).
- Entrypoints that bootstrap `web/conf.inc.php` rely on `$_SERVER['CONF_INC']` being set when running from CLI or tests.
- Some Mongo behaviors toggle `ini_set('mongo.long_as_object', true)` for 32-bit environments — be careful when calling code that expects objects vs arrays.

Examples
- Simple idql POST (via router defined in `web/bin/classes/ClassRouter.php`):

  POST /api/idql/products
  Body: application/json
  {
    "method": "find",
    "scheme": "products",
    "limit": 10,
    "page": 0,
    "where": {"status": "active"}
  }

- Programmatic query using engine helper:

  $q = new \Idae\Query\IdaeQuery('products');
  $q->setLimit(20)->setPage(0);
  $rows = $q->find(['status' => 'active']);

Development & testing
- Install deps:

  cd web/bin
  composer install

- Run unit tests (if present):

  cd web/bin
  ./vendor/bin/phpunit

- Node socket helpers live in `web/bin/node/` and can be started with the `auto_start.sh`/`auto_start.bat` scripts; for quick tests on Windows run `node web/bin/node/app_https/app_https_main.js`.

Where to change behavior
- HTTP routes: `web/bin/classes/ClassRouter.php` — add AltoRouter entries.
- API parsing: `web/bin/engine/Idae/Api/IdaeApiParser.php` — modify URI -> idql translation and supported commands.
- Query behaviour: `web/bin/engine/Idae/Query/IdaeQuery.php` — modify find/group/distinct logic or paging/sorting defaults.

If you want, I can expand any section (detailed `IdaeDataScheme` field drawers, `IdaeConnect` connection options, or example migration scripts).