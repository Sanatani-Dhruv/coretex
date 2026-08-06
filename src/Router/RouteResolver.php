<?php

namespace Dhruv125\Coretex\Router;

use Dhruv125\Coretex\Viewer\View;
use Dhruv125\Coretex\Exceptions\InternalErrorException;

use Dhruv125\Coretex\Support\Request;
use Dhruv125\Coretex\Support\Response;

class RouteResolver {

	public function __construct() {

	}

	public function resolve(callable | array | string $handler, Request $request, Response $response, ) {
		if (is_callable($handler)) {
			// pre($dynamicVariables);
			$dynamicVariables = $request->getAttribute('dynamicParams') ?? [];
			if (count($dynamicVariables)) {
				$reflect = new \ReflectionFunction($handler);
				$payload = [];
				$needParameterCount = $reflect->getNumberofParameters();
				$i = 0;
				foreach($dynamicVariables as $key => $value) {
					if (!($i++ < $needParameterCount)) {
						break;
					}
					$payload[$key] = $value;
				}
				$handler(...$payload);
			} else {
				$handler();
			}
		} elseif (is_string($handler)) {
			$dynamicVariables = $request->getAttribute('dynamicParams') ?? [];
			View::instantView($handler, $dynamicVariables);
		} elseif (is_array($handler)) {
			try {
				$paraCount = count($handler);
				switch ($paraCount) {
				case 3:
					[ $className, $methodName, $passVariables] = $handler;
					break;
				case 2:
					[ $className, $methodName ] = $handler;
					break;
				case 1:
					[ $className ] = $handler;
					// throw new InternalErrorException("Provide method name as string for $className");
					break;
				default:
					throw new InternalErrorException("Very fews arguments provided, minimum 2 are required: (1) Full Classname, (2) Method Name (default method: index)");
					break;
				}
				if (!isset($passVariables)) {
					$passVariables = [];
				}
				// $passVariables = array_merge($passVariables);
				if (!class_exists($className)) {
					throw new InternalErrorException("Trying to call Undefined class '$className'");
				}
				$object = new $className();
				// pre($dynamicVariables);

				if (!isset($methodName)) {
					if (!method_exists($object, 'index')) {
						throw new InternalErrorException("No method provided for '$className', default fallback method 'index' also not found");
					}
					return $object->{"index"}($request, $response, $passVariables);
				}
				if (!method_exists($object, $methodName)) {
					throw new InternalErrorException("Trying to call undefined method '$methodName' for class '$className'");
				}
				return $object->{$methodName}($request, $response, $passVariables);
			} catch (ViewNotFoundException $err) {
				http_response_code(500);
				echo "500 Internal Error";
			} catch(InternalErrorException $error) {
				throw $error;
			}
		}
	}
}
