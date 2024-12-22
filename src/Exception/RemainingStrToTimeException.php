<?php

namespace Mediashare\Marathon\Exception;

class RemainingStrToTimeException extends \Exception {
    public function __construct(
        string $remaining,
    ) {
        parent::__construct(
            "Remaining set [". $remaining ."] is not in correct format.",
            400,
            null
        );
    }
}