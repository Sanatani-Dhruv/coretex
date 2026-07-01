<?php

namespace Dhruv125\Coretex\Support;

use Dhruv125\Coretex\Exceptions\InternalErrorException;

class Request {
    private array $gets;
    private array $posts;
    private array $files;
    private array $requests;
    private array $cookies;

    public function __construct() {
        $this->posts = &$_POST;
        $this->gets = &$_GET;
        $this->requests = &$_REQUEST;
        $this->files = &$_FILES;
        $this->cookies = &$_COOKIE;
    }

    public function allGet() {
        return $this->gets;
    }

    public function allPost() {
        return $this->posts;
    }

    public function allCookie() {
        return $this->cookies;
    }

    public function allFile() {
        return $this->files;
    }

    public function all() {
        return [ ...$this->requests, ...$this->cookies, ...$this->files ];
    }

    public function method(): string {
        return $_SERVER['REQUEST_METHOD'] ?? "GET";
    }

    public function file(string | array $name) {
        if (is_string($name)) {
            if (isset($_FILES[$name])) {
                return $_FILES[$name];
            } else {
                return null;
            }
        } else {
            $fileArr = [];
            foreach($name as $file) {
                $fileArr[$file] = $_FILES[$file] ?? null;
            }
            return $fileArr;
        }
    }

    public function exists(string $name, bool $noValue = false) {
        if (isset($_REQUEST[$name]) && $_REQUEST[$name] !== null) {
            $var = $_REQUEST[$name];
            if(is_string($var)) {
                $var = trim($var);
                if ($noValue && $var === "") {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function input(string $method, string $var_name, int $type = FILTER_DEFAULT) : string | array | null {
        $value = null;
        switch (strtolower($method)) {
        case 'cookie':
            $value = filter_input(\INPUT_COOKIE, $var_name, $type);
            break;
        case 'session':
            // $value = filter_input(\INPUT_SESSION, $var_name, $type);
            if (isset($_SESSION[$var_name])) {
                $value = $_SESSION[$var_name];
                $value = filter_var($value, $type);
            }
            break;
        case 'server':
            $value = filter_input(\INPUT_SERVER, $var_name, $type);
            break;
        case 'request':
            $value = filter_input(\INPUT_POST, $var_name, $type);
            if ($value == null)
                $value = filter_input(\INPUT_GET, $var_name, $type);
            break;
        case 'post':
            $value = filter_input(\INPUT_POST, $var_name, $type);
            break;
        case 'get':
        default:
        $value = filter_input(\INPUT_GET, $var_name, $type);
        break;
        }

        if (is_string($value) && $value !== "") {
            $value = trim($value);
        }
        if ($value === false) {
            $value = null;
        }
        return $value;
    }

    public function getHeaders(): array {
        return getallheaders();
    }

    private function fetch(array | string $name,string $method) {
        $method = strtolower($method);
        if ($method === "get") {
            $DATA = $_GET;
        } elseif ($method === "post") {
            $DATA = $_POST;
        }
        if (is_string($name)) {
            if (isset($DATA[$name]) && $DATA[$name] !== null) {
                if (is_string($DATA[$name])) {
                    $data = trim($DATA[$name]);
                }
                if($data === "") {
                    return null;
                }
                return $data;
            } else {
                return null;
            }
        } else {
            $resultArr = [];
            foreach($name as $value) {
                if (isset($DATA[$value]) && $DATA[$value] !== null) {
                    if (is_string($DATA[$value])) {
                        $data = trim($DATA[$value]);
                    }
                    if($data === "") {
                        $resultArr[$value] = null;
                    }
                    $resultArr[$value] = $data;
                } else {
                    $resultArr[$value] = null;
                }
            }
            return count($resultArr) ? $resultArr : null;
        }


    }

    public function get(array | string $name) {
        return $this->fetch($name, "get");
    }

    public function post(string | array $name) {
        return $this->fetch($name, "post");
    }
}
