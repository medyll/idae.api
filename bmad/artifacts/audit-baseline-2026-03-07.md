# Audit baseline - 2026-03-07

Summary:
- Language: PHP
- Data store: MongoDB
- Entry points: web/index.php
- Router: web/bin/classes/ClassRouter.php
- API dispatcher: web/bin/engine/Idae/Api/IdaeApiRest.php
- Tests: web/bin/tests (phpunit)
- Docker: docker-compose.yml

Recommendations:
- Run `composer install` in web/bin
- Add CI to execute tests and fixture loader
