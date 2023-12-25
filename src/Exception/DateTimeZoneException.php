<?php

namespace Mediashare\Marathon\Exception;

class DateTimeZoneException extends \Exception {
    public function __construct(
        string $message = "DateTimeZone was incorrect",
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