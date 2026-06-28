<?php

// Pre-defined Functions By OwnWork

function approot() {
  $loc = dirname(__DIR__ . "/Helper.php",2);
  return $loc;
}

if (!function_exists("array_is_list")) {
  function array_is_list(array $array): bool {
    $i = -1;
    foreach ($array as $k => $v) {
      ++$i;
      if ($k !== $i) {
        return false;
      }
    }
    return true;
  }
}

function array_is_assoc(array $array): bool {
  return !array_is_list($array);
}

function out(string $data) {
  return htmlspecialchars($data);
}

function env($data, bool $get = true, bool $specialChars = true) {
  $data = ($specialChars) ? htmlspecialchars($data) : $data;
  return ($get) ? getenv($data) : putenv($data);
}

function getTempTranspiled(string $string, bool $needPath = true, array $args = []) {
  return Coretex\Viewer\View::includeTemp($string, $needPath, $args);
}

function view(string $string, array $args = []) {
  Coretex\Viewer\View::instantView($string, $args);
}

// Call Component
function comp(string $name, array $args = [], string $dir = "") {
  $dir = trim($dir);
  if ($dir == "") {
    $dir = approot() . "/resources/views/component/";
  }
  $fullPath = $dir . $name;

  if (file_exists($fullPath)) {
    extract($args);
    require $fullPath;
    return;
  } else {
    throw new ErrorException("Component with name: '$name' not found at $fullPath");
  }
}

function url(string $action) {
  $_SERVER['PATH_INFO'] = (!isset($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO']);
  if ($action == 'get') {
    return $_SERVER['PATH_INFO'];
  }

  if ($action == 'getFull') {
    return $_SERVER['REQUEST_URI'];
  }
}

function pre($content) {
  echo "<pre style='font-size: 1.1rem;'>";
  print_r($content);
  echo "</pre>";
}

// Function for Getting Instance of Database class if exists - If Someone Uses recommended Package i provided in DB
function get_db_instance() {
  if (file_exists(__DIR__ . "/../vendor/delight-im/db/src/PdoDataSource.php") && file_exists(__DIR__ . "/../vendor/delight-im/db/src/PdoDatabase.php")) {
    if (env("DB_DRIVER") == "sql") {
      $dataSource = new \Delight\Db\PdoDataSource(PDO_SQLITE); // see "Available drivers for database systems" below
      $dataSource->setHostname(env('DB_HOST'));
      $dataSource->setPort(3306);
      $dataSource->setDatabaseName(env('DB_NAME'));
      $dataSource->setCharset('utf8mb4');
      $dataSource->setUsername(env('DB_USER'));
      $dataSource->setPassword(env('DB_PASS'));
      $DB = \Delight\Db\PdoDatabase::fromDataSource($dataSource);
      return $DB;
    }
    if (env("DB_DRIVER") == 'sqlite') {
      $dbDir = __DIR__ . "/../storage/db";
      $dbName = env("DB_NAME") . ".db";
      $dbFilePath = "$dbDir/$dbName";

      // Creating sqlite file if not exists
      if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
      }
      if (!file_exists($dbFilePath)) {
        touch($dbFilePath);
      }

      $pdo = new PDO("sqlite:$dbFilePath");
      $DB = \Delight\Db\PdoDatabase::fromPdo($pdo);
      return $DB;
    }
  }
}
