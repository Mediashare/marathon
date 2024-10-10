<?php

namespace Mediashare\Marathon\Tests\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Trait\EntityUnserializerTrait;
use PHPUnit\Framework\TestCase;

class EntityUnserializerTraitTest extends TestCase {
    use EntityUnserializerTrait;

    public function testUnserializeConfig(): void {
        $serializedData = [
            'taskDirectory' => '/path/to/tasks',
            'taskId' => '123',
        ];

        $config = new Config();
        $config->__unserialize($serializedData);

        $this->assertEquals('/path/to/tasks', $config->getTaskDirectory());
        $this->assertEquals('123', $config->getTaskId());
    }

    public function testUnserializeTask(): void {
        $serializedData = [
            'id' => 'task123',
            'name' => 'My Task',
            'run' => false,
            'archived' => true,
            'commits' => [],
            'steps' => [],
        ];

        $task = new Task();
        $task->__unserialize($serializedData);

        $this->assertEquals('task123', $task->getId());
        $this->assertEquals('My Task', $task->getName());
        $this->assertFalse($task->isRun());
        $this->assertTrue($task->isArchived());
    }

    public function testUnserializeCommit(): void {
        $serializedData = [
            'id' => 'commit123',
            'steps' => [],
        ];

        $commit = new Commit();
        $commit->__unserialize($serializedData);

        $this->assertEquals('commit123', $commit->getId());
    }

    public function testUnserializeStep(): void {
        $serializedData = [
            'startDate' => $startDate = (new \DateTime('2023-01-01 12:00:00'))->getTimestamp(),
            'endDate' => $endDate = (new \DateTime('2023-01-01 13:00:00'))->getTimestamp(),
        ];

        $step = new Step();
        $step->__unserialize($serializedData);

        $this->assertEquals($startDate, $step->getStartDate());
    }
}