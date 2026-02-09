Tests pour idae.api

Prérequis
- PHP 7.4 avec l'extension `mongodb` installée
- MongoDB en cours d'exécution pour les tests d'intégration (ou mock)
- Composer dependencies installées: exécuter depuis `web/bin`

Installation
```bash
cd web/bin
composer install
```

Lancer les tests unitaires
```bash
cd web/bin
./vendor/bin/phpunit --testsuite unit
```

Lancer les tests d'intégration (MongoDB doit être disponible)
```bash
cd web/bin
./vendor/bin/phpunit --testsuite integration
```

Notes
- Le bootstrap `tests/bootstrap.php` inclut `conf.inc.php` si présent pour définir les constantes d'environnement.
- Pour faciliter les tests, `IdaeConnect::setInstance()` permet d'injecter une instance factice dans les tests unitaires.
