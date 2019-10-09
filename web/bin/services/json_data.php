<?
	include_once($_SERVER['CONF_INC']);

	use Idae\Rest\IdaeRestApi;

	$Rest = new IdaeRestApi();
	$Rest->parse($_REQUEST);
