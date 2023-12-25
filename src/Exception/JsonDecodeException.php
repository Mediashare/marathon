<?php

namespace Mediashare\Marathon\Exception;

class JsonDecodeException extends \Exception {
    public function __construct(
        string $filepath,
        string $className,
    ) {
        parent::__construct(
            "The file [". $filepath ."] containing the [". $className ."] object in json could not be converted to an array.",
            400,
            null
        );
    }
}