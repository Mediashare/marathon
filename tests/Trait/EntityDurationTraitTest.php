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

        $this->assertEquals('1h 30min', $duration);
    }

    public function testGetDurationForCommit(): void {
        $this->commit->addStep($this->createStep(1200)); // 20 minutes
        $this->commit->addStep($this->createStep(2400)); // 40 minutes

        $duration = $this->commit->getDuration();

        $this->assertEquals('1h', $duration);
    }

    public function testGetDurationForStep(): void {
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-01-01 13:30:00') - $startDate);

        $duration = $this->step->getDuration();

        $this->assertEquals('1h 30min', $duration);
    }

    public function testGetDurationForMonths(): void {
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-02-02 22:30:00') - $startDate);

        $duration = $this->step->getDuration();

        $this->assertEquals('32d 10h 30min', $duration);
    }

    public function testGetDurationForDays(): void {
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-01-02 22:30:00') - $startDate);

        $duration = $this->step->getDuration();

        $this->assertEquals('1d 10h 30min', $duration);
    }

    public function testGetDurationForHours(): void {
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-01-01 13:01:00') - $startDate);

        $duration = $this->step->getDuration();

        $this->assertEquals('1h 1min', $duration);
    }

    public function testGetDurationForMinutes(): void {
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-01-01 12:30:10') - $startDate);

        $duration = $this->step->getDuration();

        $this->assertEquals('30min 10s', $duration);
    }

    public function testGetDurationForSeconds(): void {
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-01-01 12:00:01') - $startDate);

        $duration = $this->step->getDuration();

        $this->assertEquals('1s', $duration);
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
        $this->step->setStartDate($startDate = strtotime('2023-01-01 12:00:00'));
        $this->step->setSeconds(strtotime('2023-01-01 13:30:00') - $startDate);

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
        $step->setSeconds($endDate - $startDate);

        return $step;
    }
}