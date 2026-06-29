<?php

namespace Dhruv125\Coretex;

use Dhruv125\Coretex\Exceptions\InternalErrorException;

class Pager {
    private string $resourceLocation;

    public function __construct(string $resourceLocation = "") {
        if ($resourceLocation === "") {
            $this->resourceLocation = approot() . "resources/appviews";
        } else {
            $this->resourceLocation = $resourceLocation;
        }

    }

    public function pageNotFoundPage(string $url = "/", string $message = "404 Page Not Found", array $moreVariables = []) {
        $error_title = $message;
        $error_message = $message;
        if(count($moreVariables)) {
            if (array_is_list($moreVariables)) {
                throw new InternalErrorException("Expecting Associative array, recieved list");
            }
            extract($moreVariables);
        }
        require_once($this->resourceLocation . "/error_layout");
    }

    public function viewNotFoundPage(string $viewName, string $message = "", array $moreVariables = []) {
        $error_title = $message;
        $error_message = $message;
        if(count($moreVariables)) {
            if (array_is_list($moreVariables)) {
                throw new InternalErrorException("Expecting Associative array for variable '\$moreVariables', recieved list");
            }
            extract($moreVariables);
        }
        require_once($this->resourceLocation . "/error_layout.php");
    }

    public function InternalErrorPage(string $message = "500 Internal Server Error", array $moreVariables = []) {
        $error_title = $message;
        $error_message = $message;
        if(count($moreVariables)) {
            if (array_is_list($moreVariables)) {
                throw new InternalErrorException("Expecting Associative array for variable '\$moreVariables', recieved list");
            }
            extract($moreVariables);
        }
        require_once($this->resourceLocation . "/error_layout.php");
    }
}
