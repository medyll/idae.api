<?php


include_once($_SERVER['CONF_INC']);

var_dump($_SERVER['REQUEST_METHOD']);
die();
$Router = new Router();
// $Router->setBasePath('/web/');