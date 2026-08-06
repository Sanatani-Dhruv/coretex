<?php

namespace Dhruv125\Coretex\Background;
use Dhruv125\Coretex\Exceptions\InternalErrorException;

class QueueClient {
    private int $pid = 0;
    private array $result;
    private array $data;
    const STORAGE_DIR = "./storage";

    private $localPayload;

    public function __construct(int $pid = 0) {
        if ($pid < 0) {
            throw new InternalErrorException("Invalid Job Process ID $pid");
        }

        $this->localPayload = [
            "pid" => $pid,
            "className" => "",
            "methodName" => "index",
            "inputData" => [],
            "result" => [],
            "status" => "pending",
        ];
    }

    public function assignId() : int {
        $maxId = $this->maxId();
        $this->localPayload["pid"] = ++$maxId;
        return $this->localPayload["pid"];
    }

    public function assignWork(string $className, string $methodName = "index") : self {
        $this->localPayload["className"] = $className;
        $this->localPayload["methodName"] = $methodName;
        return $this;
    }

    public function setData(string $key, $value) : self {
        if (!is_scalar($value)) {
            $type = gettype($value);
            if (is_object($value)) {
                $type = get_class($value);
            }
            throw new InternalErrorException("Non-scalar \$value ($type) passed, only Scalar supported");
        }
        $this->localPayload["inputData"][$key] = $value;
        print_r($this->localPayload);
        echo json_encode($this->localPayload);
        return $this;
    }

    public function unsetData(string $key) : self {
        unset($this->localPayload["inputData"][$key]);
    }

    public function cleanForUser() {
        $payload = $this->localPayload;
        unset($payload["pid"]);
        unset($payload["className"]);
        unset($payload["methodName"]);
        unset($payload["inputData"]);
        return $payload;
    }

    public function getProcess(int $pid) {

    }
}
