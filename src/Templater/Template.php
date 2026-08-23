<?php

namespace Dhruv125\Coretex\Templater;

class Template {
	private array $filePathArr;
	private $storagePath;
	private $viewStoragePath;
	private $viewResPath;

	public function __construct() {
		$this->filePathArr = array();
		$this->storagePath = approot() . "/storage/";
		$this->viewStoragePath = approot() . "/storage/views/";
		$this->viewResPath = approot() . "/resources/views/";
		$this->makeStorageDirs();
	}

	public function makeStorageDirs() {
		if (!is_dir($this->viewStoragePath)) {
			if (!is_dir($this->storagePath)) {
				mkdir($this->storagePath);
			}
			mkdir($this->viewStoragePath);
		}
		if (!is_dir($this->viewResPath)) {
			mkdir($this->viewResPath);
		}
		return;
	}

	public function parse(string $filename): string {
		if (!file_exists($filename))
			throw new \ErrorException("File '$filename' not found");

		$final = file_get_contents($filename);

		/* include and require blocks Block */
		$final = str_replace([ "@@includeTemp(", "@@includeTemp(" ], "<?php include(getTempTranspiled(", $final);
		$final = str_replace([ "@include(", "@include (" ], "<?php include(approot().'/resources/views/'.", $final);
		$final = str_replace([ "@includeRoot(", "@include (" ], "<?php include(approot(). '/'. ", $final);
		$final = str_replace([ "@include_once(", "@include_once (" ], "<?php include_once(approot().'/resources/views/'.", $final);
		$final = str_replace([ "@require(", "@require (" ], "<?php require_once(approot().'/resources/views/'.", $final);
		$final = str_replace([ "@requireRoot(", "@require (" ], "<?php require_once(approot(). '/'. ", $final);
		$final = str_replace([ "@require_once(", "@require_once (" ], "<?php require_once(approot().'/resources/views/'.", $final);

		/* PHP Block */
		$final = str_replace("@php", "<?php", $final);
		$final = str_replace([ "@endphp;\n\n", "@endphp;\n", "@endphp;", ], "?>", $final);

		/* General Ending Sequences */
		$final = str_replace(")@@", "));?>", $final);
		$final = str_replace(")@", ");?>", $final);
		$final = str_replace("):>", ") :", $final);
		$final = str_replace("):", "):?>", $final);

		/* Component Calling Directive */
		$final = str_replace([ "@comp(", "@comp (" ], "<?php comp(", $final);

		/* If Block */
		$final = str_replace([ "@if(", "@if (" ], "<?php if(", $final);
		$final = str_replace("@else:", "<?php else:?>", $final);
		$final = str_replace("@endif;", "<?php endif;?>", $final);
		$final = str_replace("@elseif(", "<?php elseif(", $final);

		/* For Block */
		$final = str_replace([ "@for(", "@for (" ], "<?php for(", $final);
		$final = str_replace("@endfor;", "<?php endfor;?>", $final);

		/* Foreach Block */
		$final = str_replace([ "@foreach(", "@foreach (" ], "<?php foreach(", $final);
		$final = str_replace("@endforeach;", "<?php endforeach;?>", $final);

		/* DeEcho Block */
		$final = str_replace("{{--", "<?php /* ", $final);
		$final = str_replace("--}}", " */?>", $final);

		/* Echo Block */
		$final = str_replace("{{", "<?=htmlspecialchars(", $final);
		$final = str_replace("}}", ")?>", $final);
		$final = str_replace("{!{", "<?=(", $final);
		$final = str_replace("}!}", ")?>", $final);

		/* While Block */
		$final = str_replace([ "@while(", "@while (" ], "<?php while(", $final);
		$final = str_replace("@endwhile;", "<?php endwhile; ?>", $final);

		/* Do While Block */
		$final = str_replace("@dowhile:", "<?php do{ ?>", $final);
		$final = str_replace("@enddowhile(", "<?php }while(", $final);

		/* Switch Block */
		$final = str_replace([ "@switch(", "@switch (" ], "<?php switch(", $final);
		$final = str_replace([ "@fcase(", "@fcase (" ], "case (", $final);
		$final = str_replace([ "@case(", "@case (" ], "<?php break;case(", $final);
		$final = str_replace("@default:", "<?php break;default:?>", $final);
		$final = str_replace("@endswitch;", "<?php break;endswitch;?>", $final);

		return $final;
	}

	public function scanRes(string $dirPath = "") {
		if (!is_dir($this->viewResPath)) {
			try {
				$this->makeStorageDirs();
			} catch (\Exception $err) {
				throw new \ErrorException("Given Path '$this->viewResPath' is not a Directory");
			}
		}

		if ($dirPath === "") {
			$dirPath = rtrim($this->viewResPath, "/");
		}

		if (is_dir($dirPath)) {
			$dirList = scandir($dirPath);
			// print_r($dirList);
			foreach($dirList as $value) {
				if ($value == "." || $value == "..")
					continue;
				// echo $dirPath . "/" . basename($value) . "\n";
				$this->scanRes($dirPath . "/" . basename($value));
			}
		} elseif(is_file($dirPath) || file_exists($dirPath)) {
			$filePath = str_replace(approot() . "/resources/views", '', $dirPath);
			if (strstr($filePath, ".temp.php")) {
				// echo "From ScanRes: " . $dirPath . "\n";
				$fromResPath = md5(dirname($filePath));

				// echo $fromResPath . "\n";
				$fileName = $fromResPath . "_" . basename($filePath,".temp.php") . "." . filemtime($this->viewResPath . "/" . $filePath) . ".c.php";
				// $fileName = $fromResPath . "_" . basename($path,".temp.php") . "." . filemtime($filePath) . ".c.php";
				// echo "From ScanRes: ";
				// echo ($fileName . "\n");
				$this->filePathArr[$filePath] = $fileName;
				// file_put_contents($this->viewStoragePath . $fileName, $this->parse($filePath));
			}
		}
		if (count($this->filePathArr)) {
			// echo "ScanRes: Total Files: " . count($this->filePathArr) . "\n";
			// print_r($this->filePathArr);
			return $this->filePathArr;
		}
		else
			return null;
	}

	public function giveCompiledViewArr() {
		return $this->filePathArr;
	}

	public function scanStorage($dirPath = "") {
		$scanFilesArr = array();
		if (!$dirPath) {
			$dirPath = $this->viewStoragePath;
		}

		if (!is_dir($dirPath)) {
			try {
				$this->makeStorageDirs();
			} catch (\Exception $err) {
				throw new \ErrorException("Given Path '$this->viewResPath' is not a Directory");
			}
		}

		$scanDir = scandir($dirPath);
		$compiledViewCount = 0;
		$resArr = $this->scanRes();
		$resourceViewCount = (is_array($resArr)) ? count($resArr) : 0;
		foreach($scanDir as $path) {
			if ($path === "." || $path === "..") {
				continue;
			}

			if (strstr($path, ".c.php")) {
				foreach($resArr as $resName => $compiledName) {
					// echo "$resName => $compiledName\n";
					if (file_exists($this->viewResPath . $resName) && strstr($path, ".c.php") && strstr($path, filemtime($this->viewResPath . $resName))) {
						$compiledViewCount++;
						// echo "$path => $resName Matched\n";
						break;
					}
				}
			}
		}

		// echo "Resource View: $resourceViewCount\nCompiled View: $compiledViewCount\n\n";
		$lessOrSameToCompiled = $resourceViewCount <= $compiledViewCount;
		/* Somehow this solved the bug of temp.php files recompiling many times */
		if ($lessOrSameToCompiled) {
			$this->clearResViewArray();
			// $this->scanRes();
		}
		return !$lessOrSameToCompiled;
	}

	public function clearResViewArray() {
		$this->filePathArr = [];
	}

	public function clearViewCache() {
		$list = scandir($this->viewStoragePath);
		foreach($list as $file) {
			if ($file === "." || $file === "..") continue;

			$filePath = $this->viewStoragePath . $file;
			if (is_file($filePath)) {
				unlink($filePath);
			}
			// echo $filePath . "\n";
		}
	}

	public function compileFiles(string $resDirName = "") {
		if (!$resDirName) {
			$basepath = $this->viewResPath;
			$arr = scandir($this->viewResPath);
		} else {
			$basepath = $resDirName;
			$arr = scandir($resDirName);
		}
		// echo "Compiled:\n";
		foreach($arr as $path) {
			if ($path === "." || $path === "..") {
				continue;
			}
			$filePath = $basepath . $path;
			// echo "Name: $path\n";
			// echo "Compiling $filePath\n";

			if (is_dir($filePath)) {
				// echo "\t> Going in Dir: $filePath\n\n";
				$this->compileFiles($filePath . "/");
				// echo "\t> Exiting in Dir: $filePath\n\n";
			}

			if (!is_dir($filePath) && strstr($path, ".temp.php")) {
				$fromResPath = str_replace(approot() . "/resources/views" , "", $filePath);
				$fromResPath = md5(dirname($fromResPath));
				// $fileDir = md5($fromResPath);
				$fileName = $fromResPath . "_" . basename($path,".temp.php") . "." . filemtime($filePath) . ".c.php";
				// echo ($fileName . "\n");
				// echo "Res File: $filePath\n";
				// echo "Compiled File: $this->viewStoragePath$fileName\n";
				file_put_contents($this->viewStoragePath . $fileName, $this->parse($filePath));
			}
		}
	}
}
