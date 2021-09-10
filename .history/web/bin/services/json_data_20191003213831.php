<?
	include_once($_SERVER['CONF_INC']);

	$Rest = new IdaeDataRest();
	$Rest->analyse($_REQUEST);
