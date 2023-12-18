<?php

namespace Mediashare\Marathon\Tests\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Task;
use PHPUnit\Framework\TestCase;

class AbstractTraitTestCase extends TestCase {
    public Task $task;
    public Commit $commit;
    public Step $step;

    protected function setUp(): void
    {
        $this->task = new Task();
        $this->commit = new Commit();
        $this->step = new Step();
    }
}