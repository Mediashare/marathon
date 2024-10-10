<?php

namespace Mediashare\Marathon\Tests\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Trait\EntityDateTimeTrait;

class EntityDateTimeTraitTest extends AbstractTraitTestCase {
    use EntityDateTimeTrait;

    public function testGetStartDateForTask(): void {
        $this->task->addCommit($this->createCommit('2023-01-01 10:00:00', '2023-01-01 11:00:00'));

        $startDate = $this->task->getStartDate();

        $this->assertEquals(strtotime('2023-01-01 10:00:00'), $startDate);
    }

    public function testGetStartDateForCommit(): void {
        $this->commit->addStep($this->createStep('2023-01-01 12:00:00', '2023-01-01 13:00:00'));

        $startDate = $this->commit->getStartDate();

        $this->assertEquals(strtotime('2023-01-01 12:00:00'), $startDate);
    }

    public function testGetStartDateForStep(): void {
        $this->step->setStartDate(strtotime('2023-01-01 14:00:00'));

        $startDate = $this->step->getStartDate();

        $this->assertEquals(strtotime('2023-01-01 14:00:00'), $startDate);
    }

    private function createCommit(string $startDate, string $endDate): Commit {
        $commit = new Commit();
        $commit->addStep($this->createStep($startDate, $endDate));
        return $commit;
    }

    private function createStep(string $startDate, string $endDate): Step {
        $step = new Step();
        $step->setStartDate(strtotime($startDate));
        $step->setEndDate(strtotime($endDate));
        return $step;
    }
}