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

	public function doIdql(array $idql = null, string $scheme = null)
	{
		if ($idql === null) {
			$idql = $this->getHttpVars();
			if ($idql === null) {
				return;
			}
		}

		$idql = array_merge([
			'method' => 'find',
			'limit'  => 10,
			'page'   => 0,
		], $idql);

		if ($scheme !== null) {
			$idql['scheme'] = $scheme;
		}

		// Pre-parse validation for direct idql payloads
		if (isset($idql['where']) && !is_array($idql['where'])) {
			// reject non-array 'where' before parser tries to transform operators
			echo $this->json_response(422, 'Invalid parameter: where must be an object');
			return;
		}

		if (empty($idql['scheme'])) {
			echo $this->json_response(422, 'Missing scheme');
			return;
		}

		try {
			$query = $this->parser->parse($idql);
		} catch (\InvalidArgumentException | \TypeError $e) {
			echo $this->json_response(422, 'Invalid query: ' . $e->getMessage());
			return;
		}

		// validate input early and return a JSON error when invalid
		if (!$this->validateQuery($query)) {
			return;
		}

		$this->process($query);
	}

	public function doRest()
	{
		if (!in_array($this->http_method, ['GET', 'HEAD', 'POST', 'PATCH', 'PUT'], true)) {
			header('Allow: GET, HEAD, POST, PATCH, PUT, OPTIONS');
			if ($this->http_method !== 'HEAD') {
				echo $this->json_response(405, 'Method not allowed');
			} else {
				http_response_code(405);
			}
			return;
		}

		try {
			$query = $this->parser->parse();
		} catch (\InvalidArgumentException | \TypeError $e) {
			echo $this->json_response(422, 'Invalid query: ' . $e->getMessage());
			return;
		}

		// validate input early and return a JSON error when invalid
		if (!$this->validateQuery($query)) {
			return;
		}

		$this->process($query);
	}

	/**
	 * Basic input validation for parsed idql/rest queries.
	 * Returns true when query is acceptable, otherwise echoes an error JSON and returns false.
	 *
	 * @param array $query
	 * @return bool
	 */
	private function validateQuery(array $query) {

		// scheme is required for all queries
		if (empty($query['scheme']) || !is_string($query['scheme'])) {
			echo $this->json_response(422, 'Missing scheme');
			return false;
		}

		// limit/page must be numeric when provided
		if (isset($query['limit']) && (!is_numeric($query['limit']) || (int)$query['limit'] < 0)) {
			echo $this->json_response(422, 'Invalid parameter: limit must be numeric');
			return false;
		}

		if (isset($query['page']) && (!is_numeric($query['page']) || (int)$query['page'] < 0)) {
			echo $this->json_response(422, 'Invalid parameter: page must be numeric');
			return false;
		}

		if (isset($query['method'])) {
			$allowed = ['find', 'findOne', 'group', 'distinct', 'parallel'];
			if (!is_string($query['method']) || !in_array($query['method'], $allowed, true)) {
				echo $this->json_response(422, 'Invalid parameter: method');
				return false;
			}
		}

		foreach (['create', 'update', 'delete'] as $writeCommand) {
			if (array_key_exists($writeCommand, $query)) {
				echo $this->json_response(422, 'Write operations are not supported by the query endpoint');
				return false;
			}
		}

		// where must be an array/object when present
		if (isset($query['where']) && !is_array($query['where'])) {
			echo $this->json_response(422, 'Invalid parameter: where must be an object');
			return false;
		}

		return true;
	}

	private function process(array $query)
	{

		$content = $this->safeDoQuery($query);
		if ($content === null) {
			return;
		}

		$this->output_method = $query['output'] ?? 'raw';

		if ($this->http_method === 'HEAD') {
			http_response_code(200);
			header('Content-Type: application/json');
			header('X-Total-Count: ' . (is_countable($content) ? count($content) : 1));
			return;
		}

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
				http_response_code(200);
				header('Content-Type: application/json');
				$return = [
					'rs' => $content,
					'options' => $this->options,
					'query' => $query,
					'record_count' => is_countable($content) ? count($content) : 1
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
		if (!empty($query['sort'])) $qy->setSort($query['sort']);

		$find         = $query['where'] ?? [];
		$query_method = $query['method'] ?? (empty($query['group']) ? 'find' : 'group');
		$query_method = empty($query['distinct']) ? $query_method : 'distinct';
		$query_method = empty($query['parallel']) ? $query_method : 'parallel';
		$projection   = $query['proj'] ?? [];

		$options = [];
		if (!empty($projection)) {
			$options['projection']                          = $projection;
			$options['projection']['id' . $query['scheme']] = 1;
		}

		// find findOne update insert ?
		$previousLongAsObject = ini_get('mongo.long_as_object');
		ini_set('mongo.long_as_object', true);
		try {
			switch ($query_method) {
			case 'find':
				$rs = $qy->find($find, $options);
				break;
			case 'findOne':
				$rs = $qy->findOne($find, $projection);
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
			default:
				throw new \InvalidArgumentException('Unsupported query method');
			}
		} finally {
			ini_set('mongo.long_as_object', $previousLongAsObject);
		}

		return $rs;
	}

	private function getHttpVars()
	{

		switch ($this->http_method) {
			case 'POST':
			case 'PATCH':
			case 'PUT':
				return $this->http_vars = $this->getJson();

			case 'GET':
				return $this->http_vars = $_GET;
		}

		return null;
	}

	private function setHttpMethod(string $http_method)
	{
		$this->http_method = $http_method;

		return $this;
	}

	private function getJson()
	{

		$contentType = isset($_SERVER["CONTENT_TYPE"]) ? strtolower(trim($_SERVER["CONTENT_TYPE"])) : '';
		$contentType = trim(explode(';', $contentType)[0]);

		switch ($contentType) {
			case 'application/x-www-form-urlencoded':
				return $this->http_vars = $_POST;
			case 'application/json':
				$content = trim(file_get_contents("php://input"));
				$decoded = json_decode($content, true);

				if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
					echo $this->json_response(400, 'Invalid JSON');
					return null;
				}

				return $this->http_vars = $decoded;
		}

		echo $this->json_response(415, 'Unsupported media type');
		return null;
	}

	private function json_response($code = 200, $message = null)
	{
		// clear the old headers
		//header_remove();
		http_response_code($code);
		// set the header to make sure cache is forced
		// header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
		header('Content-Type: application/json');

		return json_encode([
			'status'  => $code < 300,
			'message' => $message,
		]);
	}
	private function safeDoQuery(array $query)
	{
		try {
			return $this->doQuery($query);
		} catch (\Throwable $e) {
			error_log('Idae query failure: ' . $e->getMessage());
			if ($this->http_method !== 'HEAD') {
				echo $this->json_response(500, 'Query execution failed');
			} else {
				http_response_code(500);
				header('Content-Type: application/json');
			}

			return null;
		}
	}
}

