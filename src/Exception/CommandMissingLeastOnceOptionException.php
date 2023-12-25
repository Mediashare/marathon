<?php

namespace Mediashare\Marathon\Exception;

class CommandMissingLeastOnceOptionException extends \Exception {
    public function __construct(
        string $commandName,
        array $options,
    ) {
        parent::__construct(
            "the [". $commandName ."] command must contain at least one of the following options [". implode(", ", $options)."].",
            400,
            null,
        );
    }
}