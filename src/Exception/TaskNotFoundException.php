<?php

namespace Mediashare\Marathon\Exception;

class TaskNotFoundException extends \Exception {
    public function __construct(
        string $message = "Task session was not found",
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