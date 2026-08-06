<?php

namespace Dhruv125\Coretex\Background;
use Dhruv125\Coretex\Exceptions\InternalErrorException;

class Queue {
    private int $pid = -1;
    private array $result;
    private array $data;
    private array $handler;
    const STORAGE_DIR = "./storage";

    private $localPayload;

    public function __construct(int $pid = 0) {
        if ($pid < 0) {
            throw new InternalErrorException("Invalid Job Process ID $pid");
        }

    }

    public function getId() : int {
        return $this->localPayload["pid"];
    }

    public function makeStorageDirs(string $directory = self::STORAGE_DIR) {
        if (is_file($directory)) {
            throw new InternalErrorException("File with path '$directory' already exists! Not able to create directory");
        }
        if (is_dir($directory)) {
            return $directory;
        }
        echo "Making Directory '" . self::STORAGE_DIR . "'\n";
        return mkdir(self::STORAGE_DIR);
    }

    public function maxId() {
        $content = file_get_contents(self::STORAGE_DIR . "/maxid");
        if (!is_int($content)) {
            file_put_contents(self::STORAGE_DIR . "/maxid", "1");
            return 1;
        }

        $content = (int) $content;
        return $content;
    }

    public function write() : self {

    }

    public function exec() : string {
        $this->localPayload['result'] = shell_exec("ls ~");
        return $this->localPayload["result"];
    }
}
