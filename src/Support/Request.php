<?php

namespace Coretex\Support;

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
        return $_SERVER['REQUEST_METHOD'];
    }

    public function file(string $name, int | bool $raw = false) {
        if (isset($_FILES[$name]) && $_FILES[$name]) {
            $data = trim($_FILES[$name]);
            if (!$raw) {
                $data = htmlspecialchars($data);
            }
            return $data;
        } else {
            return null;
        }
    }

    public function exists(string $name, bool $noValue = false) {
        if (isset($_REQUEST[$name])) {
            if (!$_REQUEST[$name] && $noValue) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getInput(string $method, string $var_name, int $type = FILTER_DEFAULT, bool $raw = false):string | array | null {
        $value = null;
        switch (strtolower($method)) {
        case 'cookie':
            $value = filter_input(\INPUT_COOKIE, $var_name, $type);
            break;
        case 'session':
            $value = filter_input(\INPUT_SESSION, $var_name, $type);
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

        $value = trim($value);
        if (!$raw) {
            $value = htmlspecialchars($value);
        }
        return ($value) ? $value : null;
    }

    public function getInputRaw(string $method, string $var_name, int $type = FILTER_DEFAULT):string | array | null {
        return $this->getInput($method, $var_name, $type, true);
    }

    public function getHeaders(): array {
        return headers_list();
    }

    public function get(string $name, int | bool $raw = false) {
        if (isset($_GET[$name]) && $_GET[$name]) {
            $data = trim($_GET[$name]);
            if (!$raw) {
                $data = htmlspecialchars($data);
            }
            return $data;
        } else {
            return null;
        }
    }

    public function getRaw(string $name) {
        return $this->get($name, true);
    }

    public function post(string $name, int | bool $raw = false) {
        if (isset($_POST[$name]) && $_POST[$name]) {
            $data = trim($_POST[$name]);
            if (!$raw) {
                $data = htmlspecialchars($data);
            }
            return $data;
        } else {
            return null;
        }
    }

    public function postRaw(string $name) {
        return $this->post($name, true);
    }
}
