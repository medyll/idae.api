# AGENTS.md - Development Guidelines for idae.api

This file provides essential guidelines for agentic coding agents working in this repository.

## Project Overview

idae.api is a PHP-based REST API framework with MongoDB backend, designed to run under Apache/nginx with document root at `web/`. The project uses a router → API parser → query layer pattern and includes Docker support for development and testing.

## Build/Lint/Test Commands

### PHP Dependencies
```bash
cd web/bin && composer install
```

### Running Tests
```bash
# All tests
cd web/bin && ./vendor/bin/phpunit

# Unit tests only (fast)
cd web/bin && ./vendor/bin/phpunit --testsuite unit

# Integration tests (requires MongoDB)
cd web/bin && ./vendor/bin/phpunit --testsuite integration

# Single test file
cd web/bin && ./vendor/bin/phpunit tests/unit/ExampleTest.php

# Single test method
cd web/bin && ./vendor/bin/phpunit --filter testMethodName tests/unit/ExampleTest.php
```

### Docker Environment
```bash
docker compose up -d --build
# Test in Docker
docker compose exec app bash -lc "cd /var/www/html/web/bin && ./vendor/bin/phpunit --testsuite unit"
```

## Code Style Guidelines

### PHP Conventions
- **Namespaces**: Use `Idae\Api`, `Idae\Query`, `Idae\App` namespaces
- **Autoloading**: PSR-4 with `web/bin/vendor/autoload.php`
- **Class Structure**: Follow existing patterns in `web/bin/engine/Idae/`
- **Error Handling**: Use consistent JSON responses through `IdaeApiRest::json_response()`
- **Headers**: CORS enabled with `Access-Control-Allow-Origin: *`

### Key Patterns
- **Router Pattern**: Extend `AltoRouter` with route definitions in `ClassRouter::routes()`
- **API Pattern**: `IdaeApiRest` → `IdaeApiParser` → `IdaeQuery` → MongoDB
- **Configuration**: Environment variables with fallbacks in `constants.php`

### Import Guidelines
```php
// Use statements at top
use Idae\Api\IdaeApiRest;
use Idae\Query\IdaeQuery;
use AltoRouter;

// Include configuration when needed
require_once __DIR__ . '/../conf.inc.php';
```

### Naming Conventions
- **Classes**: PascalCase (e.g., `IdaeApiRest`, `ClassRouter`)
- **Methods**: camelCase (e.g., `doIdql`, `json_response`)
- **Variables**: camelCase (e.g., `$api`, `$uri_vars`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `MDB_HOST`)

### Database Operations
- Use `IdaeQuery` for all MongoDB operations
- Query format: array with keys like `scheme`, `method`, `limit`, `page`, `where`, `proj`, `parallel`
- Response formats: `raw`, `raw_casted`, `html` (controlled by `output_method`)

## File Organization

```
web/
├── index.php              # Main entry point
├── conf.inc.php           # Configuration and headers
├── bin/                   # Core application code
│   ├── classes/           # Base classes (Router, Session, etc.)
│   ├── engine/Idae/       # Main API engine (Api, Query, Connect, etc.)
│   ├── tests/              # PHPUnit tests
│   ├── config/             # Configuration files
│   └── vendor/            # Composer dependencies
├── examples/              # API usage examples
└── docker-compose.yml     # Docker configuration
```

## Development Workflow

### Setup Process
1. Clone repository
2. `docker compose up -d --build`
3. `cd web/bin && composer install`
4. Run tests to verify setup

### Adding New Features
1. **Add Routes**: Modify `ClassRouter::routes()` with new endpoints
2. **Implement Logic**: Add classes under `web/bin/engine/Idae/` following existing patterns
3. **Test**: Use PHPUnit for unit tests, curl examples for manual testing
4. **Docker**: Test in containerized environment for consistency

### Important Gotchas
- **Environment Setup**: Many files expect `$_SERVER['CONF_INC']` or to include `web/conf.inc.php`
- **Autoload**: Always use `web/bin/vendor/autoload.php`
- **Response Format**: Keep compatible responses with `json_encode(..., JSON_PRETTY_PRINT)`
- **MongoDB**: Use centralized access in `Idae\Query\IdaeQuery.php`

## Testing Guidelines

### Test Structure
- **Unit Tests**: `tests/unit/` - Fast, no external dependencies
- **Integration Tests**: `tests/integration/` - Requires MongoDB
- **Fixtures**: `tests/fixtures/` - Test data and fixture loader

### Running Individual Tests
```bash
# Single test file
./vendor/bin/phpunit tests/unit/IdaeApiParserTest.php

# Single test method
./vendor/bin/phpunit --filter testDoIdql tests/unit/IdaeApiParserTest.php

# Specific test suite
./vendor/bin/phpunit --testsuite unit
```

## API Conventions

### Request Patterns
- **IDQL Endpoint**: `POST /api/idql/[scheme]` with JSON payloads
- **REST Endpoint**: `GET|POST|PATCH|PUT /api/*` for CRUD operations

### Response Formats
```json
// raw format
{
  "data": [...],
  "count": 10,
  "total": 100
}

// raw_casted format
{
  "items": [...],
  "pagination": {"page": 0, "limit": 10, "total": 100}
}
```

## Node.js Components

- **Socket/Cron Helpers**: Located in `web/bin/node/`
- **Start Scripts**: `auto_start.sh` (Linux) or direct node execution (Windows)
- **Dependencies**: Socket.io, MongoDB driver, cron

## Where to Make Changes

- **HTTP Endpoints**: Modify `ClassRouter::routes()`
- **Business Logic**: Implement/extend under `web/bin/engine/Idae`
- **Configuration**: Update `conf.inc.php` or `constants.php`
- **Tests**: Add to appropriate test suite in `tests/`

## Security Considerations

- **CORS**: Already enabled globally
- **Input Validation**: Use existing parser patterns in `IdaeApiParser`
- **Database Access**: Always use `IdaeQuery` layer for security
- **Error Handling**: Never expose stack traces in production

## Performance Guidelines

- **MongoDB Queries**: Use appropriate indexes for frequent queries
- **Parallel Operations**: Leverage `parallel` option for batch operations
- **Caching**: Consider implementing caching for frequently accessed data
- **Response Size**: Use pagination (`limit`, `page`) for large datasets

## Documentation

- **API Usage**: Check `examples/README.md` for curl commands
- **Architecture**: Review `.github/copilot-instructions.md` for detailed guidance
- **Code Comments**: Follow existing commenting patterns in the codebase

## Common Issues

- **Missing Dependencies**: Always run `composer install` in `web/bin`
- **MongoDB Connection**: Ensure MongoDB is running and accessible
- **CORS Issues**: Check `conf.inc.php` for header configuration
- **Test Failures**: Verify MongoDB is available for integration tests

# context-mode — MANDATORY routing rules

You have context-mode MCP tools available. These rules are NOT optional — they protect your context window from flooding. A single unrouted command can dump 56 KB into context and waste the entire session.

## BLOCKED commands — do NOT attempt these

### curl / wget — BLOCKED
Any shell command containing `curl` or `wget` will be intercepted and blocked by the context-mode plugin. Do NOT retry.
Instead use:
- `context-mode_ctx_fetch_and_index(url, source)` to fetch and index web pages
- `context-mode_ctx_execute(language: "javascript", code: "const r = await fetch(...)")` to run HTTP calls in sandbox

### Inline HTTP — BLOCKED
Any shell command containing `fetch('http`, `requests.get(`, `requests.post(`, `http.get(`, or `http.request(` will be intercepted and blocked. Do NOT retry with shell.
Instead use:
- `context-mode_ctx_execute(language, code)` to run HTTP calls in sandbox — only stdout enters context

### Direct web fetching — BLOCKED
Do NOT use any direct URL fetching tool. Use the sandbox equivalent.
Instead use:
- `context-mode_ctx_fetch_and_index(url, source)` then `context-mode_ctx_search(queries)` to query the indexed content

## REDIRECTED tools — use sandbox equivalents

### Shell (>20 lines output)
Shell is ONLY for: `git`, `mkdir`, `rm`, `mv`, `cd`, `ls`, `npm install`, `pip install`, and other short-output commands.
For everything else, use:
- `context-mode_ctx_batch_execute(commands, queries)` — run multiple commands + search in ONE call
- `context-mode_ctx_execute(language: "shell", code: "...")` — run in sandbox, only stdout enters context

### File reading (for analysis)
If you are reading a file to **edit** it → reading is correct (edit needs content in context).
If you are reading to **analyze, explore, or summarize** → use `context-mode_ctx_execute_file(path, language, code)` instead. Only your printed summary enters context.

### grep / search (large results)
Search results can flood context. Use `context-mode_ctx_execute(language: "shell", code: "grep ...")` to run searches in sandbox. Only your printed summary enters context.

## Tool selection hierarchy

1. **GATHER**: `context-mode_ctx_batch_execute(commands, queries)` — Primary tool. Runs all commands, auto-indexes output, returns search results. ONE call replaces 30+ individual calls.
2. **FOLLOW-UP**: `context-mode_ctx_search(queries: ["q1", "q2", ...])` — Query indexed content. Pass ALL questions as array in ONE call.
3. **PROCESSING**: `context-mode_ctx_execute(language, code)` | `context-mode_ctx_execute_file(path, language, code)` — Sandbox execution. Only stdout enters context.
4. **WEB**: `context-mode_ctx_fetch_and_index(url, source)` then `context-mode_ctx_search(queries)` — Fetch, chunk, index, query. Raw HTML never enters context.
5. **INDEX**: `context-mode_ctx_index(content, source)` — Store content in FTS5 knowledge base for later search.

## Output constraints

- Keep responses under 500 words.
- Write artifacts (code, configs, PRDs) to FILES — never return them as inline text. Return only: file path + 1-line description.
- When indexing content, use descriptive source labels so others can `search(source: "label")` later.

## ctx commands

| Command | Action |
|---------|--------|
| `ctx stats` | Call the `stats` MCP tool and display the full output verbatim |
| `ctx doctor` | Call the `doctor` MCP tool, run the returned shell command, display as checklist |
| `ctx upgrade` | Call the `upgrade` MCP tool, run the returned shell command, display as checklist |
