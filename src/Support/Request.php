<?php

namespace Dhruv125\Coretex\Support;

use Dhruv125\Coretex\Exceptions\InternalErrorException;

class Request {
    private array $gets;
    private array $posts;
    private array $files;
    private array $requests;
    private array $cookies;
    private array $server;

    public function __construct() {
        $this->posts = $_POST;
        $this->gets = $_GET;
        $this->requests = $_REQUEST;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->server = $_SERVER;
    }

    public function allGet() : array {
        return $this->gets;
    }

    public function allPost() : array {
        return $this->posts;
    }

    public function allCookie() : array {
        return $this->cookies;
    }

    public function allFile() : array {
        return $this->files;
    }

    public function allServer() : array {
        return $this->server;
    }

    public function all() : array {
        return [
            'requests' => $this->requests,
            'cookies' => $this->cookies,
            'files' => $this->files,
            'gets' => $this->gets,
            'posts' => $this->posts,
            'server' => $this->server,
        ];
    }

    public function method(): string {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function file(string | array $name) {
        if (is_string($name)) {
            if (isset($this->files[$name])) {
                return $this->files[$name];
            } else {
                return null;
            }
        } else {
            $fileArr = [];
            foreach($name as $file) {
                $fileArr[$file] = $this->files[$file] ?? null;
            }
            return $fileArr;
        }
    }

    public function has(string $key) : bool {
        return isset($this->requests[$key]);
    }

    public function missing(string $key) : bool {
        return !$this->has($key);
    }

    public function recursiveMap(array $array, callable $callback) : array {
        $resultArr = [];
        foreach($array as $key => $value) {
            if (is_array($value)) {
                $resultArr[$key] = $this->recursiveMap($value, $callback);
                continue;
            }
            $resultArr[$key] = $callback($value);
        }
        return $resultArr;
    }

    public function filled(string $key) : bool {
        $has = $this->has($key);
        if (!$has) {
            return false;
        }

        $value = $this->requests[$key] ?? null;
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== "";
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return true;
    }

    public function input(string $source, string $key) : mixed {
        $source = strtolower($source);

        $data = match($source) {
            'get' => $this->gets[$key] ?? null,
            'post' => $this->posts[$key] ?? null,
            'cookie' => $this->cookies[$key] ?? null,
            'request' => $this->requests[$key] ?? null,
            'server' => $this->server[$key] ?? null,
            default => throw new InternalErrorException("Unsupported source '$source' provided!"),
        };

        if (is_string($data) && $source !== 'server') {
            $data = trim($data);
        }

        if (is_array($data)) {
            $data = array_map(fn($value) => is_string($value) ? trim($value) : $value, $data);
        }

        return $data;
    }

    private function trim(mixed $string, string $character_mask) {
        if (is_string($string)) {
            return trim($string, $character_mask);
        }
        return $string;
    }

    public function getHeaders(): array {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach($this->server as $key => $value) {
            if (str_starts_with($key, "HTTP_")) {
                $header = str_replace('_', '-', substr($key, 5));
            } elseif(in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $header = str_replace('_', '-', $key);
            } else {
                continue;
            }

            $header = ucwords(strtolower($header), '-');

            /* HTTP_USER_AGENT => USER-AGENT */
            $headers[$header] = $value;
        }
        return $headers;
    }

    private function fetch(array | string $name, string $method) : mixed {
        $method = strtolower($method);
        $_DATA = match($method) {
            'get' => $this->gets,
            'post' => $this->posts,
            'cookie' => $this->cookies,
            default => throw new InternalErrorException("Unsupported method '$method' provided!"),
        };
        if (is_string($name)) {
            if (isset($_DATA[$name]) && $_DATA[$name] !== null) {
                $data = $_DATA[$name];
                if (is_string($data)) {
                    $data = trim($data);
                }
                return $data;
            } else {
                return null;
            }
        } else {
            $resultArr = [];
            foreach($name as $value) {
                if (!isset($_DATA[$value]) || $_DATA[$value] === null) {
                    $resultArr[$value] = null;
                    continue;
                }
                $data = $_DATA[$value];
                if (is_string($data)) {
                    $data = trim($data);
                }
                if (is_array($data)) {
                    $data = array_map(fn($value) => is_string($value) ? trim($value) : $value, $data);
                }
                $resultArr[$value] = $data;
            }
            return count($resultArr) ? $resultArr : null;
        }


    }

    public function get(array | string $name) : mixed {
        return $this->fetch($name, 'get');
    }

    public function post(string | array $name) : mixed {
        return $this->fetch($name, 'post');
    }
}
