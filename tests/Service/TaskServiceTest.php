<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TaskNotFoundException;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TaskService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class TaskServiceTest extends AbstractTestCase {
    private TaskService $taskService;

    protected function setUp(): void
    {
        $config = new Config(
            taskDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'tasks',
            taskId: (new \DateTime())->format('YmdHis')
        );
        $this->taskService = (new TaskService(new StepService()))->setConfig($config);
    }

    /**
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     */
    public function testCreate(): void {
        $task = $this->taskService->create(['id' => 'test_id'])->getTask();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('test_id', $task->getId());
    }

    /**
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     */
    public function testGetTask(): void {
        $task = $this->taskService->getTask();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertTrue($task->isRun());
    }

    /**
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     */
    public function testStart(): void {
        $task = $this->taskService->start('Test Task', '+1 hour')->getTask();

        $this->assertTrue($task->isRun());
        $this->assertEquals('Test Task', $task->getName());
        $this->assertCount(2, $task->getSteps());

        $step = $task->getSteps()->first();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertTrue($step->getEndDate() > $step->getStartDate());

        $step = $task->getSteps()->last();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNull($step->getEndDate());
    }

    /**
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     */
    public function testStop(): void {
        $this->taskService->start('Test Task', '+1 hour');
        $task = $this->taskService->stop()->getTask();

        $this->assertFalse($task->isRun());
        $this->assertCount(2, $task->getSteps());

        $step = $task->getSteps()->first();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertTrue($step->getEndDate() > $step->getStartDate());

        $step = $task->getSteps()->last();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertSame($step->getEndDate(), $step->getStartDate());
    }

    /**
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     */
    public function testArchive(): void {
        $this->taskService->start('Test Task', '+1 hour');
        $task = $this->taskService->archive()->getTask();

        $this->assertTrue($task->isArchived());
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     */
    public function testDelete(): void {
        $this->taskService->start('Test Task', '+1 hour');
        $this->taskService->delete();

        $this->expectException(TaskNotFoundException::class);
        $this->taskService->getTask(createItIfNotExist: false);
    }
}