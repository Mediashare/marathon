<?php

namespace Mediashare\Marathon\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Task;

trait ArrayToEntityTrait {
    public function arrayToEntity(
        array $array,
        string $className
    ): Config|Task|Commit|Step {
        return unserialize(sprintf(
            'O:%d:"%s"%s',
            strlen($className),
            $className,
            strstr(
                serialize($array),
                ':'
            )
        ), [
            Config::class,
            Task::class,
            Commit::class,
            Step::class,
        ]);
    }
}