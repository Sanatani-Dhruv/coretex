<?php

namespace Dhruv125\Coretex\Support;

class Response {
    private array $payload = [];
    private string $body = '';
    private array $headers = [];
    private int $statusCode = 200;

    public function __construct() {

    }

    public function isJson(bool | int $isJson = true) : self {
        if ($isJson) {
            $this->setContentType('application/json');
        } else {
            unset($this->headers['Content-Type']);
        }
        return $this;
    }

    public function setContentType(string $type, string $charset = 'UTF-8') : self{
        $this->headers['Content-Type'] = "$type; charset=$charset";
        return $this;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function setHeader(string $name, string $value, bool $replace = true): self {
        if ($replace) {
            $this->headers[$name] = $value;
        } else {
            if (!isset($this->headers[$name])) {
                $this->headers[$name] = [];
            } elseif(!is_array($this->headers[$name])) {
                $this->headers[$name] = [$this->headers[$name]];
            }

            $this->headers[$name][] = $value;
        }
        return $this;
    }

    public function setHeaders(array $headers, bool $replace = true) : self {
        foreach($headers as $key => $value) {
            if ($replace) {
                $this->headers[$key] = $value;
            } else {
                if (!isset($this->headers[$key])) {
                    $this->headers[$key] = [];
                } elseif(!is_array($this->headers[$key])) {
                    $this->headers[$key] = [$this->headers[$key]];
                }

                $this->headers[$key][] = $value;
            }
        }
        return $this;
    }

    public function setPayload(string | array $payload, string | int | array | bool | float $payloadValue = null) : self {
        if (is_array($payload)) {
            foreach ($payload as $key => $value){
                $this->payload[$key] = $value;
            }
        } elseif(is_string($payload) && $payloadValue !== null) {
            $this->payload[$payload] = $payloadValue;
        }
        return $this;
    }

    public function getPayload():array {
        return $this->payload;
    }

    public function setCode(int $code) : self {
        $this->statusCode = $code;
        return $this;
    }

    public function getCode() : int {
        return $this->statusCode;
    }

    public function setBody(string $body) : self {
        $this->body = $body;
        return $this;
    }

    public function getBody() : string {
        return $this->body;
    }

    public function json(array $data, int $statusCode = 200) : self {
        return $this
            ->setCode($statusCode)
            ->setContentType('application/json')
            ->setBody(json_encode($data, JSON_THROW_ON_ERROR));
    }

    public function html(string $html, int $statusCode = 200) : self {
        return $this
            ->setCode($statusCode)
            ->setContentType('text/html')
            ->setBody($html);
    }

    public function noContent() : self {
        return $this
            ->setCode(204)
            ->setBody('');
    }

    public function dispatch() : void {
        http_response_code($this->statusCode);

        foreach($this->headers as $name => $value) {
            if (is_array($value)) {
                $multiValue = $value;
                foreach($multiValue as $headerValue) {
                    header("$name: $headerValue", false);
                }
                continue;
            }
            header("$name: $value");
        }

        if (in_array($this->statusCode, [204, 304], true)) {
            return;
        }

        if ($this->body === "" && count($this->payload)) {
            $this->body = json_encode($this->payload, JSON_THROW_ON_ERROR);
        }

        echo $this->body;
    }

}
