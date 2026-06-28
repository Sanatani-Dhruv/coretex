<?php
namespace Bundle;

use Bundle\Environment\Environment;
use Bundle\Handler\GlobalErrorHandler;
use Coretex\Exceptions\PageNotFoundException;
use Coretex\Exceptions\ViewNotFoundException;

class Bundler {
	function __construct() {
		if (file_exists(__DIR__ . "/../.env") && file_exists(__DIR__ . "/../vendor/autoload.php")) {
			require __DIR__ . '/../vendor/autoload.php';
		} else {
			http_response_code(500);
			if (file_exists(__DIR__ . "/../resources/appviews/no-info-error.php")) {
				$error_title = htmlspecialchars("Setup Command Needed");
				$error_message = htmlspecialchars("Run Setup Command First!");
				$error_description = "Command:<br><code>composer run setup</code>";
				require(__DIR__ . "/../resources/appviews/no-info-error.php");
			}
			else
				echo "No Setup Command Ran!! Run <code>composer run setup</code>";
			die();
		}

		// Loading .env file from Root Directory
		$environment = new Environment();
		$errorLevel = $environment->setenv();

		// var_dump(getenv('OWNWORK_ERROR_HANDLER'));
		// $doHandler = strtolower(getenv('OWNWORK_ERROR_HANDLER'));
		// if($doHandler == "true" || $doHandler == "1") {
		// 	require(__DIR__ . "/ErrorHandler/Handler.php");
		// }

		// Global Helper Functions exist in below file
		//
		// Error Handler Setup for Application

		$handler = new GlobalErrorHandler($errorLevel);
	}

	public function bundle() {
		try {
			require_once(__DIR__ . "/Routes.php");
		} catch (PageNotFoundException $err) {
			http_response_code(404);
			echo "404 Not Found";
		} catch (Exception $err) {
			echo $err;
		}
	}
}
