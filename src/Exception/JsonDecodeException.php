<?php

namespace Mediashare\Marathon\Exception;

class JsonDecodeException extends \Exception {
    public function __construct(
        string $filepath,
        string $message = "Json format was corrupted",
        int $code = 404,
        \Throwable|null $previous = null,
    ) {
        parent::__construct(
            '['.$filepath.'] ' . $message,
            $code,
            $previous
        );
    }
}