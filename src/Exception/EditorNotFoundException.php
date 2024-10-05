<?php

namespace Mediashare\Marathon\Exception;

class EditorNotFoundException extends \Exception {
    public function __construct() {
        parent::__construct(
            "The default editor was not found.",
            404,
            null,
        );
    }
}