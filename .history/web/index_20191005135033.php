<?php

die();
include_once($_SERVER['CONF_INC']);

var_dump($_SERVER);
$Router = new Router();
$router->setBasePath('/web/');