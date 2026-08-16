<?php
namespace Dhruv125\Coretex\Router;

use Dhruv125\Coretex\Exceptions\PageNotFoundException;
use Dhruv125\Coretex\Exceptions\InternalErrorException;
use Dhruv125\Coretex\Router\RouteResolver;

use Dhruv125\Coretex\Support\Request;
use Dhruv125\Coretex\Support\Response;

class Route {
	private array $requests;
	private array $globalMiddleware;
	private bool $matchFound;
	private RouteResolver $resolver;
	private Request $request;
	public string $currentUrl;

	public function __construct() {
		// echo "--- Made Router ---<br>";
		// echo "================<br>";
		$this->requests = [];
		$this->requests['GET'] = null;
		$this->requests['POST'] = null;
		$this->requests['DELETE'] = null;
		$this->requests['PATCH'] = null;
		$this->requests['PUT'] = null;
		$this->resolver = new RouteResolver();
		$this->matchFound = false;
		$this->request = new Request();
		$this->currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$this->globalMiddleware = [];
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
				$result['currentRoute'] = $url;
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
			$result['currentRoute'] = $url;
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

	public function get(string $url, callable | array | string $handler) {
		$this->requests['GET'][$url] = [
			'handler' => $handler,
			'middlewares' => []
		];
		return $this->requests['GET'];
	}

	public function globalMiddleware(array | callable $handler, array $params = []) {
		if (is_array($handler)) {
			[ $className, $methodName ] = $handler;
			$this->globalMiddleware[] = [
				'class' => $className,
				'method' => $methodName ?? "index",
				'params' => $params ?? [],
			];
		} else {
			$this->globalMiddleware[] = [
				'handler' => $handler,
				'params' => $params ?? [],
			];
		}
		return $this->globalMiddleware;
	}

	public function getGlobalMiddleware(): array {
		return $this->globalMiddleware;
	}

	public function middleware(string $method, string $url, array | callable $handler) {
		$method = strtoupper($method);
		// $this->requests[$method][$url]['middlewares'][] = [
		// 	'class' => $className,
		// 	'method' => $methodName ?? 'index',
		// ];
		if (is_array($handler)) {
			$count = count($handler);
			if (!$count) {
				throw new InternalErrorException("No handler class provided for route: '$url', request method: '$method'");
			} elseif ($count === 1) {
				$handler[1] = "index";
			}

			$this->requests[$method][$url]['middlewares'][] = $handler;
		} elseif (is_callable($handler)) {
			// pre($this);
			$this->requests[$method][$url]['middlewares'][] = $handler;
		}
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
		$requestMethod = $this->requests[$_SERVER['REQUEST_METHOD']] ?? $this->requests["GET"] ?? [];

		foreach($requestMethod as $request => $content) {
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
				'params' => $result['params'],
				'currentRoute' => $result['currentRoute'] ?? null,
			];
			// $this->resolver->resolve($currentUrl, $handler, $keyPair);
		}
		return $result;

	}

}
