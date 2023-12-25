<?php

namespace Mediashare\Marathon\Exception;

class StartDateFormatException extends \Exception {
    public function __construct(
        string $message = "StartDate format was incorrect",
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