<?php

namespace Dhruv125\Coretex\Handler;

class GlobalErrorHandler {
	private string $systemCompDir;

	public function __construct (int $errorLevel) {
		$this->systemCompDir = (approot() . "/resources/appviews/");
		if (isset($_ENV['OWNWORK_ERROR_HANDLER']) && $_ENV['OWNWORK_ERROR_HANDLER']) {
			set_error_handler([&$this, 'HandleError'], $errorLevel);
			set_exception_handler([&$this, 'HandleException']);
		}
	}

	private function comp(string $compName, array $parameters = []) {
		\comp($compName, $parameters, $this->systemCompDir);
	}

	public function HandleError ($Code, $Message, $File = null, $Line = 0, $Context = []) {
		// echo "Working Error";
		error_reporting(E_ALL ^ E_DEPRECATED);
		if (isset($_ENV['DEV_ENV']) && $_ENV['DEV_ENV']) {
			$errFile = (str_contains($File, approot()) ? str_replace(approot() . "/", "", $File) : $File);

			$this->comp("error_layout.php", [
				"errMsg" => $Message,
				"errFile" => $errFile,
				"errLine" => $Line,
				"syscompdir" => $this->systemCompDir,
				"envArr" => $_ENV
			]);
		} else {
			http_response_code(500);
			if (file_exists(approot() . "/resources/appviews/no-info-error.php")) {
				$error_title = "500 Internal Server Error";
				$error_message = $error_title;
				require(approot() . "/resources/appviews/no-info-error.php");
			} else {
				echo "500 Internal Server Error";
			}
		}
		exit;
		// Handle error here.
	}
	private function getTempFileName(string $compiledFile): string {
		if (str_contains($compiledFile,"compiled.php")) {
			$errFile = "";
			$compiledFile = basename($compiledFile);
			if (file_exists(approot() . "/storage/views.json")) {
				$viewArr = json_decode(file_get_contents(approot() . "/storage/views.json"), true);
				foreach($viewArr as $key => $value) {
					if ($value == $compiledFile) {
						$errFile = $key;
					}
				}
				if ($errFile === "") {
					return $compiledFile;
				} else {
					return $errFile;
				}
			} else {
				return $compiledFile;
			}
		} else {
			return $compiledFile;
		}
	}

	private function printTracePath(&$Exception) {
		$traceBlockArr = array();
		foreach($Exception->getTrace() as $trace) {
			$traceContainer = "";
			$fileContent = fopen($trace["file"], "r");
			$traceContainer .= "<pre class='file_content p-2 overflow-auto hidden'>";
			$i = 0;

			while ($line = fgets($fileContent)) {
				$i++;
				if (!trim($line) !== "") {
					if (($trace["line"] >= $i - 4 && $trace["line"] < $i+5)) {
						if ($trace['line'] === $i) {
							$traceContainer .= "<div class='text-red-500/100'><strong><em>";
							$traceContainer .= "$i ";
							$traceContainer .= out($line);
							$traceContainer .= "</strong></em></div>";
						} else {
							$traceContainer .= "<div>";
							$traceContainer .= "$i ";
							$traceContainer .= out($line);
							$traceContainer .= "</div>";
						}
					}
				}
			}
			$traceContainer .= "</pre>";
			// print_r($trace);
			$traceBlockArr[] = $traceContainer;
			$traceContainer = "";
			fclose($fileContent);
		}
		return $traceBlockArr;
	}

	private function getEnvArrBlocks(array $envArr = []) {
		if (!count($envArr)) {
			$envArr = $_ENV;
		}

		return $envArr;
	}

	private function getErrorFileLines(string $errFile, int $errLine): string | bool {
		if (file_exists($errFile)) {
			$fileContent = fopen($errFile, "r");
			$i = 0;
			$outputArr = "";
			$outputArr .= "<pre>";

			while ($line = fgets($fileContent)) {
				$line = out($line);
				$i++;
				if ($errLine >= $i - 4 && $errLine < $i+5) {
					if ($errLine === $i) {
						$outputArr .= "<span class='text-red-500/100'><strong><em>$i " . rtrim($line) . " <span class='text-gray-500/100'>" . out("<- ERROR") . "</span>\n" . "</em></strong></span>";
					} else {
						$outputArr .= "<span class=''>$i $line</span>";
					}
				}
			}
			$outputArr .= "</pre>";
		} else {
			throw new \ErrorException("File $errFile not found");
		}
		return $outputArr;
	}

	public function HandleException($Exception) {
		if (isset($_ENV['DEV_ENV']) && $_ENV['DEV_ENV']) {
			http_response_code(500);
			try {
				$traceBlockArr = null;
				// echo "!!Working Exception!!";

				if ($Exception->getTrace()) {
					$traceBlockArr = $this->printTracePath($Exception);
				}
				// echo "<pre>";
				// print_r(get_class_methods($Exception));
				// echo "</pre>";

				$errFile = $this->getTempFileName($Exception->getFile());
				$errFileCompiled = "";
				if ($errFile != $Exception->getFile()) {
					$errFileCompiled = str_replace(approot() . "/", "", $Exception->getFile());
				}

				$envArrBlock = $this->getEnvArrBlocks();

				/* Remove System dir Prefix which is app directory */
				$errFile = (str_contains($errFile, approot()) ? str_replace(approot() . "/", "", $errFile) : $errFile);
				$tracePathArr = array_map(
					function($trace) {
						return (str_contains($trace["file"], approot()) ?
							str_replace(approot() . "/", "" , $trace['file'])
							:
							$trace["file"]
						);
					}, $Exception->getTrace());

				$errLine = $Exception->getLine();
				$errLinesArray = $this->getErrorFileLines(approot() . "/" . $errFile, $errLine);

				if (!file_exists(approot() . "/storage/error.log")) {
					if (!is_dir(approot() . "/storage/")) {
						mkdir(approot() . "/storage/");
					}
					touch(approot(). "/storage/error.log");
				}
				$error_log = "[" . date(DATE_RSS) . "] - MESSAGE: {$Exception->getMessage()} | FILE: {$Exception->getFile()} | LINE_NUMBER: $errLine |\nSTACK_TRACE:\n";
				$i = 1;
				foreach($Exception->getTrace() as $trace) {
					$error_log .= "$i) {$trace['file']} : {$trace['line']}\n";
					$i++;
				}
				$error_log .= "=================\n";
				unset($i);
				error_log($error_log, 3, approot() . "/storage/error.log");

				$this->comp("error_layout.php", [
					"errMsg" => $Exception->getMessage(),
					"errFile" => $errFile,
					"errFileCompiled" => $errFileCompiled,
					"errLine" => $errLine,
					"errLinesArray" => $errLinesArray,
					"traceBlocksArr" => $traceBlockArr,
					"syscompdir" => $this->systemCompDir,
					"tracePathArr" => $tracePathArr,
					"envArr" => $envArrBlock
				]);
				exit();

				// Handle exception here.
			} catch (\ErrorException $err) {
				echo($err);
			}
		}
		else {
			http_response_code(500);
			if (file_exists(approot() . "/resources/appviews/no-info-error.php")) {
				$error_title = "500 Internal Server Error";
				$error_message = $error_title;
				require(approot() . "/resources/appviews/no-info-error.php");
			} else {
				echo "500 Internal Server Error";
			}
		}
	}
};
