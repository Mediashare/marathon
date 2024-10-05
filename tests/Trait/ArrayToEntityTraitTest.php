<?php

namespace Mediashare\Marathon\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Mediashare\Marathon\Trait\ArrayToEntityTrait;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;

class ArrayToEntityTraitTest extends TestCase {
    use ArrayToEntityTrait;

    public function testArrayToEntityWithConfig(): void {
        $array = [
            'dateTimeFormat' => 'Y-m-d H:i:s',
            'taskDirectory' => '/path/to/tasks',
            'taskId' => '123',
        ];

        $result = $this->arrayToEntity($array, Config::class);

        $this->assertInstanceOf(Config::class, $result);
        $this->assertEquals('Y-m-d H:i:s', $result->getDateTimeFormat());
        $this->assertEquals('/path/to/tasks', $result->getTaskDirectory());
        $this->assertEquals('123', $result->getTaskId());
    }

    public function testArrayToEntityWithTask(): void {
        $array = [
            'id' => 'task123',
            'name' => 'My Task',
            'run' => false,
            'archived' => true,
        ];

        $result = $this->arrayToEntity($array, Task::class);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('task123', $result->getId());
        $this->assertEquals('My Task', $result->getName());
        $this->assertFalse($result->isRun());
        $this->assertTrue($result->isArchived());
    }

    public function testArrayToEntityWithCommit(): void {
        $array = [
            'id' => 'commit123',
            'message' => 'Initial commit',
        ];

        $result = $this->arrayToEntity($array, Commit::class);

        $this->assertInstanceOf(Commit::class, $result);
        $this->assertEquals('commit123', $result->getId());
        $this->assertEquals('Initial commit', $result->getMessage());
    }

    public function testArrayToEntityWithStep(): void {
        $array = [
            'startDate' => $startDate = (new \DateTime('2023-01-01 12:00:00'))->getTimestamp(),
            'endDate' => $endDate = (new \DateTime('2023-01-01 12:30:00'))->getTimestamp(),
        ];

        $result = $this->arrayToEntity($array, Step::class);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertEquals($startDate, $result->getStartDate());
        $this->assertEquals($endDate, $result->getEndDate());
    }
}