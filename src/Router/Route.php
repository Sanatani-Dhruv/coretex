<?php
namespace Dhruv125\Coretex\Router;

use Dhruv125\Coretex\Exceptions\PageNotFoundException;
use Dhruv125\Coretex\Router\RouteResolver;

use Dhruv125\Coretex\Support\Request;
use Dhruv125\Coretex\Support\Response;

class Route {
	private array $requests;
	private bool $matchFound;
	private RouteResolver $resolver;
	private Request $request;
	public string $currentUrl;

	public function __construct() {
		// echo "--- Made Router ---<br>";
		// echo "================<br>";
		$this->requests = [];
		$this->resolver = new RouteResolver();
		$this->matchFound = false;
		$this->request = new Request();
		$this->currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	}

	private function matchRoute(string $url): array {
		$result = [
			'matched' => false,
			'params' => [],
		];
		// echo "===================<br>";

		$regex = '/{([\w\/]*)}/m';
		$replaceRegex = '([\\w]{1,})';
		$dynamicVar = [];
		if (!str_contains($url, '{')) {
			if ($this->currentUrl === $url) {
				$result['matched'] = true;
				return $result;
			}
		}

		preg_match_all($regex, $url, $dynamicVar);
		array_shift($dynamicVar);
		$dynamicVar = $dynamicVar[0] ?? null;

		$requestRegex = preg_replace($regex, $replaceRegex, $url);
		$requestRegex = str_replace('/', '\/', $requestRegex);
		$requestRegex = '/^' . $requestRegex . '$/';

		if (preg_match($requestRegex, $this->currentUrl)) {
			$result['matched'] = true;
			preg_match_all($requestRegex, $this->currentUrl, $variableValues);
			array_shift($variableValues);

			$i = 0;
			foreach($dynamicVar as $key) {
				$keyPair[$key] = $variableValues[$i++][0];
			}
			$result['params'] = $keyPair;
		}

		return $result;
	}

	private function runMiddleware(string $currentTempUrl, string | array | callable $finalHandler, array $keyPair = []) {
		$middlewares = $this->requests[$this->request->method()][$currentTempUrl]['middlewares'];
		$payload = [];
		foreach($middlewares as $middleware) {
			$payload[] = $middleware($keyPair);
		}
		pre($payload);
	}

	public function get(string $url, callable | array | string $handler) {
		$this->requests['GET'][$url] = [
			'handler' => $handler,
			'middlewares' => []
		];
		return $this->requests['GET'];
	}

	public function middleware(string $method, string $url, callable $handler) {
		$method = strtoupper($method);
		$this->requests[$method][$url]['middlewares'][] = $handler;
		// $this->parse_url_temp($url);
	}

	public function post(string $url, callable | array | string $handler) {
		$this->requests['POST'][$url] = [
			'handler' => $handler,
			'middlewares' => []
		];
		return $this->requests['POST'];
	}

	public function put(string $url, callable | array | string $handler) {
		$this->requests['PUT'][$url] = [
			'handler' => $handler,
			'middlewares' => []
		];
		return $this->requests['PUT'];
	}

	public function delete(string $url, callable | array | string $handler) {
		$this->requests['DELETE'][$url] = [
			'handler' => $handler,
			'middlewares' => []
		];
		return $this->requests['DELETE'];
	}

	public function patch(string $url, callable | array | string $handler) {
		$this->requests['PATCH'][$url] = [
			'handler' => $handler,
			'middlewares' => []
		];
		return $this->requests['PATCH'];
	}

	public function end() {

		foreach($this->requests[$_SERVER['REQUEST_METHOD']] as $request => $content) {
			$result = $this->matchRoute($request);
			if ($result['matched']) {
				break;
			}
		}

		if ($result['matched']) {
			// echo "Matched Url: $this->currentUrl<br>";
			if (!isset($keyPair)) {
				$keyPair = [];
			}
			// pre($keyPair);

			return [
				'middlewares' => $content['middlewares'],
				'handler' => $content['handler'],
				'params' => $result['params']
			];
			// $this->resolver->resolve($currentUrl, $handler, $keyPair);
		}
		return $result;

	}

}
