<?
	include_once($_SERVER['CONF_INC']);

	use Idae\Rest\IdaeDataRest;

	$Rest = new IdaeDataRest();
	$Rest->parse($_REQUEST);
