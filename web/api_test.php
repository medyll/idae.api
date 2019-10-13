<?
	include_once($_SERVER['CONF_INC']);

	use Idae\Api\IdaeApiQuery;

	// where:in[idclient]=[62068,62069]&or[price]=10&or[quantity]=[lte:20,eq:12,search:$search_string$]&or[color]=red$

	$idql = ['scheme' => 'agent',
	         'limit'  => 10,
	         'page'   => 0,
	         'sort'   => 'idagent',
	         'where'  => ['ne' => ['idagent' => 1],
	                      'in' => ['idagent' => [10, 9, 8, 7, 6]],
	                      'or' => ['price'    => 18,
	                               'color'    => 'red$',
	                               'quantity' => ['lte' => 20, 'gte' => 10]]],
	];
	// var_dump(http_build_query($idql));
	echo IdaeApiQuery::idql($idql, 'get');
	echo IdaeApiQuery::get('scheme:client/limit:10/page:0/sort:idagent:1/where:ne[idagent]=1');
