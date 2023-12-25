<?php

namespace Mediashare\Marathon\Exception;

class StrToTimeDurationException extends \Exception {
    public function __construct(
        string $message = "Duration format was incorrect",
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