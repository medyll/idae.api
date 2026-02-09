<?php

namespace Idae\Api;

use \parallel;

use Idae\App\IdaeAppBase;
use Idae\Data\Scheme\Field\Element\IdaeDataSchemeFieldElement;
use Idae\Data\Scheme\Field\Fabric\IdaeDataSchemeFieldDrawerFabric;
use Idae\Query\IdaeQuery;
use function class_exists;
use function file_get_contents;
use function header;
use function is_array;
use function json_decode;
use function json_encode;
use function sizeof;
use function str_replace;
use function trim;
use function uniqid;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;

/**
 * Created by PhpStorm.
 * User: Mydde
 * Date: 07/06/2018
 * Time: 22:12
 *
 * point d'entrée du listener
 */
class IdaeApiRest
{

	private $api_root = '/api/';
	private $http_method;
	private $query_method;
	/** @var string $query_method */
	private $output_method;
	private $http_vars;
	private $parser;
	private $options;

	/**
	 * IdaeApiRest constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{

		$this->setHttpMethod($_SERVER['REQUEST_METHOD']);
		$this->getHttpVars();

		$this->parser = new IdaeApiParser();

		$this->options = array_merge($options, [
			'api_root'     => $this->api_root,
			'request_uri'  => $_SERVER['REQUEST_URI'],
			'qy_code_type' => 'php'
		]);

		$this->parser->setApiRoot($this->options['api_root'])
			->setRequestUri(str_replace($this->options['api_root'], '', $this->options['request_uri']))
			->setQyCodeType($this->options['qy_code_type']);
	}

	public function doIdql(array $idql = null)
	{

		$idql = $idql ?? $this->http_vars;

		$query = $this->parser->parse($idql);

		$this->process($query);
	}

	public function doRest()
	{
		$query = $this->parser->parse();
		$this->process($query);
	}

	private function process(array $query)
	{

		$content = $this->doQuery($query);

		switch ($this->output_method) {
			case 'html':
				if (class_exists('\Idae\Cast')) {
					return 'casted';
				}

				break;
			case 'raw_casted':

				$data          = new IdaeAppBase();
				$scheme_fields = $data->getSchemeFieldList($query['scheme']);

				/*$fabric->fetch_query($content, 'find');
						$fields = $fabric->get_templateDataRaw(); */

				$arr_tmp  = [];
				$new_data = [];
				foreach ($content as $index => $row_data) {
					$row_data = (array)$row_data;
					foreach ($scheme_fields as $key => $arr_field) {
						$erzrez              = new IdaeDataSchemeFieldElement($arr_field, $row_data, $query['scheme'], 'draw_cast_field');
						$codeField           = $erzrez->field_code ?: uniqid();
						$arr_tmp[$codeField] = $erzrez->value_to_raw;
					}
					$new_data[] = array_merge($row_data, $arr_tmp);
				}
				echo "<pre>" . json_encode($new_data, JSON_PRETTY_PRINT, JSON_PRESERVE_ZERO_FRACTION) . "</pre>";
				break;
			case 'steam':
				return 'stream';
			case 'raw':
			default:
				header('content-type:application/json');
				$this->json_response(200, 'OK');
				$return = [
					'rs' => $content,
					'options' => $this->options,
					'query' => $query,
					'record_count' => sizeof($content)
				];
				echo json_encode($return, JSON_PRETTY_PRINT, JSON_PRESERVE_ZERO_FRACTION);
				break;
		}
	}

	/**
	 * @param mixed $query_method
	 *
	 * @return IdaeApiRest
	 */
	public function setQueryMethod($query_method)
	{
		$this->query_method = $query_method;

		return $this;
	}




	private function doQuery(array $query)
	{

		$qy = new IdaeQuery();
		$qy->collection($query['scheme']);

		if (!empty($query['limit'])) $qy->setLimit($query['limit']);
		if (!empty($query['page'])) $qy->setPage($query['page']);
		if (!empty($query['sort'])) $qy->setSort((int)$query['sort']);

		$find         = $query['where'] ?? [];
		$query_method = empty($query['group']) ? 'find' : 'group';
		$query_method = empty($query['distinct']) ? $query_method : 'distinct';
		$query_method = empty($query['parallel']) ? $query_method : 'parallel';
		$projection   = $query['proj'] ?? [];

		$options = [];
		if (!empty($projection)) {
			$options['projection']                          = $projection;
			$options['projection']['id' . $query['scheme']] = 1;
		}

		// find findOne update insert ?
		ini_set('mongo.long_as_object', true);
		switch ($query_method) {
			case 'find':
				$rs = $qy->find($find, $options);
				break;
			case 'group':
				$rs = $qy->group($query['group'], $find, $projection); // $options => $projection 2023
				break;
			case 'distinct':
				$rs = $qy->distinct($query['distinct'], $find); // $options
				break;
			case 'parallel':

				//$runtime = new \parallel\Runtime();
				/* $promises = array();
				$tasks = array(function () { 
				}, function () { 
				}); */

				/* foreach ($tasks as $task) {
					$promises[] = $runtime->run($task);
				} */

				//$future = \parallel\when($promises);

				/* $future->then(function ($results) {
					echo "All promises results :";
					var_dump($results);
				}); */

				foreach ($query['parallel'] as $index => $qy) {
					$nQy = $query['parallel'][$index];
					$nQy['scheme'] = $query['parallel'][$index]['scheme'] ?? $query['scheme'];
					$rs[$index] = $this->doQuery($nQy);
				}
				break;
		}
		ini_set('mongo.long_as_object', false);
		// var_dump($rs);

		return $rs;
	}

	private function getHttpVars()
	{

		switch ($this->http_method) {
			case 'POST':
			case 'PATCH':
			case 'PUT':
				$this->http_vars = $this->getJson();
				break;

			case 'GET':
				$this->http_vars = $_GET; // $this->http_vars = $_REQUEST;
		}
	}

	private function setHttpMethod(string $http_method)
	{
		$this->http_method = $http_method;

		return $this;
	}

	private function getJson()
	{

		$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

		switch ($contentType) {
			case 'application/x-www-form-urlencoded':
				return $this->http_vars = $_POST;
				break;
			case 'application/json':
				$content = trim(file_get_contents("php://input"));
				$decoded = json_decode($content, true);

				if (!is_array($decoded)) {
					// throw new Exception('Invalid JSON!');
					$this->json_response(400, 'Invalid JSON!');

					return [];
				}

				return $this->http_vars = $decoded;
				break;
		}
	}

	private function json_response($code = 200, $message = null)
	{
		// clear the old headers
		//header_remove();
		http_response_code($code);
		// set the header to make sure cache is forced
		// header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
		header('Content-Type: application/json');

		$status = [
			200 => '200 OK',
			400 => '400 Bad Request',
			422 => 'Unprocessable Entity',
			500 => '500 Internal Server Error',
		];

		header('Status: ' . $status[$code]);

		return json_encode([
			'status'  => $code < 300,
			'message' => $message,
		]);
	}
}
