<?php

namespace Mediashare\Marathon\Exception;

class StrToTimeDurationException extends \Exception {
    public function __construct(
        string $duration,
    ) {
        parent::__construct(
            "Duration set [". $duration ."] is not in correct format.",
            400,
            null
        );
    }
}