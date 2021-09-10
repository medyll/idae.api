<?php


include_once($_SERVER['CONF_INC']);

var_dump($_SERVER['REQUEST_URI']);
$Router = new Router();
$router->setBasePath('/web/');