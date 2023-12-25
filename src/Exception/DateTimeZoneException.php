<?php

namespace Mediashare\Marathon\Exception;

class DateTimeZoneException extends \Exception {
    public function __construct(
        string $dateTimeZone,
    ) {
        parent::__construct(
            "DateTimeZone [". $dateTimeZone ."] was incorrect.",
            400,
            null
        );
    }
}