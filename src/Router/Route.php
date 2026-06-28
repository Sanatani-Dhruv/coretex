<?php
namespace Dhruv125\Coretex\Router;

use Dhruv125\Coretex\Exceptions\PageNotFoundException;
use Dhruv125\Coretex\Router\RouteResolver;

class Route {
	private array $requests;
	private bool $matchFound;
	private RouteResolver $resolver;

	public function __construct() {
		// echo "--- Made Router ---<br>";
		// echo "================<br>";
		$this->requests = [];
		$this->resolver = new RouteResolver();
		$this->matchFound = false;
	}

	public function get(string $url, callable | array | string $handler) {
		$this->requests['GET'][$url] = $handler;
		return $this->requests['GET'];
	}

	public function post(string $url, callable | array | string $handler) {
		$this->requests['POST'][$url] = $handler;
		return $this->requests['POST'];
	}

	public function put(string $url, callable | array | string $handler) {
		$this->requests['PUT'][$url] = $handler;
		return $this->requests['PUT'];
	}

	public function delete(string $url, callable | array | string $handler) {
		return $this->requests['DELETE'];
	}

	public function patch(string $url, callable | array | string $handler) {
		return $this->requests['DELETE'];
	}

	public function middleware(array $mapping = [], callable $handler = null): bool | int {
		pre($mapping);
		return 0;
	}

	public function end() {
		// pre($this->requests);
		$regex = '/{([\w\/]*)}/m';
		$replaceRegex = '([\\w]{1,})';
		$currentUrl = parse_url($_SERVER['REQUEST_URI']);
		if (array_key_exists('path', $currentUrl)) {
			$currentUrl = $currentUrl['path'];
		}

		// echo "Current Url: $currentUrl<br>";
		$variableArray;
		foreach($this->requests[$_SERVER['REQUEST_METHOD']] as $request => $handler) {
			// echo "Request: ' $request '<br><br>";
			if ($this->matchFound) break;
			$normalMatch = true;
			if (str_contains($request, '}')) {
				$normalMatch = false;
				preg_match_all($regex, $request, $dynamicVar);
				array_shift($dynamicVar);
				$dynamicVar = $dynamicVar[0];
				// pre($dynamicVar);
				$requestRegex = preg_replace($regex, $replaceRegex, $request);
				$requestRegex = str_replace('/', '\/', $requestRegex);
				$requestRegex = '/^' . $requestRegex . '$/';

				// echo "Match Against This Regex: `$requestRegex`<br>";
			}
			if (!$normalMatch) {
				if (preg_match($requestRegex, $currentUrl)) {
					preg_match_all($requestRegex, $currentUrl, $variableValues);
					array_shift($variableValues);
					// pre($variableValues);

					$i = 0;
					$keyPair = [];
					foreach($dynamicVar as $key) {
						$keyPair[$key] = $variableValues[$i][0];
						$i++;
					}
					// pre($keyPair);
					// echo "Current Page: '$request'<br>";
					$this->matchFound = true;
					break;
				}
			} else {
				if ($currentUrl === $request) {
					$this->matchFound = true;
					break;
				}
			}

			// echo "Handler: ";
			// pre($handler);
			// echo "===========<br>";
			// echo "===========<br>";
		}

		if ($this->matchFound) {
			// echo "Matched Url: $currentUrl<br>";
			if (!isset($keyPair)) {
				$keyPair = [];
			}
			// pre($keyPair);
			$this->resolver->resolve($currentUrl, $handler, $keyPair);
		} else {
			throw new PageNotFoundException("404 Page Not Found");
		}
	}

}
