<?php

namespace Coretex\Support;

class Response {
    public array $payload;
    private string $payloadJson;

    public function __construct() {
        $this->payload = [];
    }

    public function isJson(bool | int $isJson = true) {
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header_remove('Content-Type');
        }
    }

    public function setContentType(string $type): void {
        header("Content-Type: $type");
    }

    public function getHeaders(): array {
        return headers_list();
    }

    public function setHeader(string $name, string $value, bool $replace = true): void {
        header("$name: $value", $replace);
    }

    public function setHeaders(array $headerList) {
        if (array_is_assoc($headerList)) {
            foreach($headerList as $key => $value) {
                header_remove($key);
                header("$key: $value");
            }
        }
    }

    public function setPayload(string | array $payload, string | int | array | bool | float $payloadValue = null):bool {
        if (is_array($payload) && array_is_assoc($payload)) {
            foreach ($payload as $key => $value){
                $this->payload[$key] = $value;
            }
            return true;
        } elseif(is_string($payload) && $payloadValue !== null) {
            $this->payload[$payload] = $payloadValue;
            return true;
        }
        return false;
    }

    public function setCode(int $code):int {
        return http_response_code(404);
    }

    public function getPayload():array {
        return $this->payload;
    }

    public function dispatchJsonPayload(bool $setHeader = false) {
        if ($setHeader) {
            header_remove('Content-Type');
            $this->isJson(true);
        }
        echo json_encode($this->payload);
    }

}
