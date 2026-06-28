<?php
namespace Dhruv125\Coretex\Viewer;

use Dhruv125\Coretex\Router\Route;
use Dhruv125\Coretex\Exceptions\ViewNotFoundException;

/*! This Class handles calling views, should not be modified, unless you know what you do.*/
class View {
	private $uri;
	private $viewLocation;

	/*! By the way, this doesn't do anything, better use helper function view() */
	function __construct() {
		//
	}

	/*! Object Oriented wrapper for static method instantView()
	 * @param string $viewName viewname relative to approot() . '/resources/views/'
	 * @param array $keyValue associative array to pass variables to pass to view file
	 * 
	 * @return void
	 * */
	public function view(string $viewName, array $keyValue = []): void {
		$this::instantView($viewName, $keyValue);
	}

	/*! static function to either include template view or get it's transpiled file's path
	 * @param string $tempName template viewname relative to approot() . '/resources/views/'
	 * @param string $givePath wheter want path or include it at place (default: false)
	 * @param array $pairs associative array to pass variables to template view file
	 * 
	 * @return void
	 * */
	public static function includeTemp(string $tempName, bool $givePath = false, array $pairs = []) {
		$approot = approot();
		$compiledViewStorageDir = $approot . "/storage/views/";
		$compiledViewDetailPath = $approot . "/storage/views.json";

		if (!file_exists($compiledViewDetailPath)) {
			throw new \ErrorException("views.json not found at " . dirname($compiledViewDetailPath));
		}
		$compiledViewDetails = json_decode(file_get_contents($compiledViewDetailPath), true);
		$compiledViewPath = $compiledViewStorageDir . $compiledViewDetails[$approot . "/resources/views/" . $tempName];
		if (file_exists($compiledViewPath)) {
			if (count($pairs)) extract($pairs);
			if (!$givePath) {
				require($compiledViewPath);
				return;
			} else {
				return $compiledViewPath;
			}
		} else {
			$error_message = out("View with name `$tempName` not Found.");
			throw new ViewNotFoundException($tempName, $error_message);
		}
	}

	/*! static function called by Object Oriented Wrapper to call views can internally call includeTemp() method
	 * @param string $viewName viewname relative to approot() . '/resources/views/'
	 * @param array $keyValue associative array to pass variables to pass to view file
	 * 
	 * @return void
	 * */
	public static function instantView(string $viewName, array $keyValue = []) {
		$viewLocation = approot() . "/resources/views/";

		if (file_exists($viewLocation . $viewName)) {
			if (count($keyValue)) {
				extract($keyValue);
			}
			if (strstr($viewName, "temp.php")) {
				self::includeTemp($viewName, false, $keyValue);
			} else {
				require($viewLocation . $viewName);
			}
		} else {
			$error_message = out("View with name `$viewName` not Found.");
			throw new ViewNotFoundException($viewName, $error_message);
		}
	}
}
