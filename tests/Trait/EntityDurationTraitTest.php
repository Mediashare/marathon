<?php

namespace Mediashare\Marathon\Tests\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Trait\EntityDurationTrait;

class EntityDurationTraitTest extends AbstractTraitTestCase {
    use EntityDurationTrait;

    public function testGetDurationForTask(): void {
        $this->task->addCommit($this->createCommit(3600)); // 1 hour
        $this->task->addStep($this->createStep(1800)); // 30 minutes

        $duration = $this->task->getDuration();

        $this->assertEquals('01:30:00', $duration);
    }

    public function testGetDurationForCommit(): void {
        $this->commit->addStep($this->createStep(1200)); // 20 minutes
        $this->commit->addStep($this->createStep(2400)); // 40 minutes

        $duration = $this->commit->getDuration();

        $this->assertEquals('01:00:00', $duration);
    }

    public function testGetDurationForStep(): void {
        $this->step->setStartDate(strtotime('2023-01-01 12:00:00'));
        $this->step->setEndDate(strtotime('2023-01-01 13:30:00'));

        $duration = $this->step->getDuration();

        $this->assertEquals('01:30:00', $duration);
    }

    public function testGetSecondsForTask(): void {
        $this->task->addCommit($this->createCommit(3600)); // 1 hour
        $this->task->addStep($this->createStep(1800)); // 30 minutes

        $seconds = $this->task->getSeconds();

        $this->assertEquals(5400, $seconds);
    }

    public function testGetSecondsForCommit(): void {
        $this->commit->addStep($this->createStep(1200)); // 20 minutes
        $this->commit->addStep($this->createStep(2400)); // 40 minutes

        $seconds = $this->commit->getSeconds();

        $this->assertEquals(3600, $seconds);
    }

    public function testGetSecondsForStep(): void {
        $this->step->setStartDate(strtotime('2023-01-01 12:00:00'));
        $this->step->setEndDate(strtotime('2023-01-01 13:30:00'));

        $seconds = $this->step->getSeconds();

        $this->assertEquals(5400, $seconds);
    }

    private function createCommit(int $durationInSeconds): Commit {
        $commit = new Commit();

        $step = $this->createStep($durationInSeconds);
        $commit->addStep($step);

        return $commit;
    }

    private function createStep(int $durationInSeconds): Step {
        $step = new Step();

        $startDate = strtotime('2023-01-01 00:00:00');
        $endDate = $startDate + $durationInSeconds;
        $step->setStartDate($startDate);
        $step->setEndDate($endDate);

        return $step;
    }
}