<?
	include_once($_SERVER['CONF_INC']);

	use Idae\Api\IdaeApiQuery;

	// where:in[idclient]=[62068,62069]&or[price]=10&or[quantity]=[lte:20,eq:12,search:$search_string$]&or[color]=red$

	/*	$idql = ['method' => 'find',
				 'scheme' => 'agent',
				 'limit'  => 10,
				 'page'   => 0,
				 'sort'   => 'idagent',
				 'where'  => ['ne' => ['idagent' => 1],
							  'in' => ['idagent' => [10, 9, 8, 7, 6]],
							  'or' => ['price'    => 18,
									   'color'    => 'red$',
									   'quantity' => ['lte' => 20, 'gte' => 10]]],
		];*/
	$idql = ['method'       => 'find',
	         'scheme'       => 'appscheme',
	         'query_method' => 'findOne',
	         'limit'        => 10,
	         'page'         => 0,
	         'sort'         => 'idagent',
	         'where'        => ['ne' => ['idagent' => 1],
	         ]];
	// var_dump(http_build_query($idql));
	$rs_1 = IdaeApiQuery::idql($idql);
	/*$rs_2 = IdaeApiQuery::query('scheme:client/limit:10/page:0/sort:idagent:1/where:ne[idagent]=1');*/

	echo "<pre>$rs_1</pre>";
	//echo "<pre>$rs_2</pre>";
