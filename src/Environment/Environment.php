<?php

namespace Bundle\Environment;

class Environment {
  # Set Environmental variables

  # Path Compared to this directory

  public static function setenv() {
    $dotEnvironment = new DotEnvironment(approot() . "/.env");

    $dev_env = ($_ENV["DEV_ENV"] == 'true') ? true : false;

    $errorLevel = E_ALL;
    if ($dev_env) {
      $errorLevel = E_ALL ^ E_DEPRECATED;
      ini_set('display_errors', '1');
      ini_set('display_startup_errors', '1');
      error_reporting($errorLevel);
    }
    return $errorLevel;
  }
}

?>
