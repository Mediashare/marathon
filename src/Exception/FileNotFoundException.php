<?php

namespace Mediashare\Marathon\Exception;

class FileNotFoundException extends \Exception {
    public function __construct(
        string $filepath,
        string $className,
    ) {
        parent::__construct(
            "The file [". $filepath ."] was not found for [". $className ."] object.",
            404,
            null
        );
    }
}