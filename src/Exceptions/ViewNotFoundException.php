<?php

namespace Coretex\Exceptions;

class ViewNotFoundException extends \Exception {
    public $viewName;
    public $message;

    public function __construct($viewName, $message = "View Not Found", $code = 0, ?Throwable $previous = null) {
        $this->viewName = $viewName;
        $this->message = $message;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return parent::__toString();
    }
}
