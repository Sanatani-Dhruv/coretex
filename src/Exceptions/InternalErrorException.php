<?php

namespace Coretex\Exceptions;

class InternalErrorException extends \Exception {
    public function __construct($message, $code = 0, ?Throwable $previous = null) {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return parent::__toString();
    }
}

