<?php

namespace Mediashare\Marathon\Exception;

class MissingParameterException extends \Exception {
    public function __construct(
        string $message = "Missing parameter(s)",
        int $code = 404,
        \Throwable|null $previous = null,
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}