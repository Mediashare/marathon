<?php

namespace Mediashare\Marathon\Exception;

class TaskNotFoundException extends \Exception {
    public function __construct(
        string|null $taskId = null,
    ) {
        parent::__construct(
            "Task ID ". $taskId ? "[". $taskId ."] " : null ."session was not found",
            404,
            null,
        );
    }
}