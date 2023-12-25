<?php

namespace Mediashare\Marathon\Exception;

class CommitNotFoundException extends \Exception {
    public function __construct(
        string $commitId,
    ) {
        parent::__construct(
    "Commit ID [". $commitId ."] was not found.",
    404,
    null,
        );
    }
}