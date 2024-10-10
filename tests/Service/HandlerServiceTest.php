<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\StrToTimeDurationException;
use Mediashare\Marathon\Exception\TaskNotFoundException;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\SerializerService;
use Mediashare\Marathon\Service\TaskService;

class HandlerServiceTest extends AbstractServiceTestCase {
    private HandlerService $handlerService;
    private TaskService $taskService;

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws \JsonException
     */
    public function setUp(): void {
        parent::setUp();

        $config = $this->createMock(Config::class);
        $config->method('getConfigPath')->willReturn($this->configPath);
        $config->method('getTaskDirectory')->willReturn($this->taskDirectory);

        $configService = $this->createMock(ConfigService::class);
        $configService->method('setConfig')->willReturnSelf();
        $configService->method('write')->willReturnSelf();
        $configService->method('getConfig')->willReturn($config);

        $this->handlerService = (new HandlerService(
            $configService,
            $this->taskService = $this->createMock(TaskService::class),
            $this->createMock(CommitService::class),
            $this->createMock(SerializerService::class),
        ))->writeConfig(
            configPath: $this->configPath,
            taskDirectory: $this->taskDirectory,
        );
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws \JsonException
     */
    public function testSetAndGetConfig(): void {
        $this->handlerService->writeConfig(false, false, false, false);

        $this->assertInstanceOf(Config::class, $this->handlerService->getConfig());
    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function testStartTask(): void {
        $task = new Task();
        $this->taskService->method('setConfig')->willReturnSelf();
        $this->taskService->method('start')->willReturnSelf();
        $this->taskService->method('update')->willReturnSelf();
        $this->taskService->method('getTask')->willReturn($task);

        $task = $this->handlerService->taskStart()->getTask();

        $this->assertTrue($task->isRun());
        $this->assertInstanceOf(Task::class, $task);

    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function testStopTask(): void {
        $task = (new Task())->setRun(false);
        $this->taskService->method('setConfig')->willReturnSelf();
        $this->taskService->method('stop')->willReturnSelf();
        $this->taskService->method('getTask')->willReturn($task);

        $task = $this->handlerService->taskStop()->getTask();

        $this->assertFalse($task->isRun());
        $this->assertInstanceOf(Task::class, $task);
    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function testArchiveTask(): void {
        $task = (new Task())->setArchived(true);
        $this->taskService->method('setConfig')->willReturnSelf();
        $this->taskService->method('archive')->willReturnSelf();
        $this->taskService->method('update')->willReturnSelf();
        $this->taskService->method('getTask')->willReturn($task);

        $task = $this->handlerService->taskArchive()->getTask();

        $this->assertTrue($task->isArchived());
        $this->assertInstanceOf(Task::class, $task);
    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws StrToTimeDurationException
     */
    public function testDeleteTask(): void {
        $task = new Task();

        $this->taskService->method('setConfig')->willReturnSelf();
        $this->taskService->method('start')->willReturnSelf();
        $this->taskService->method('getTask')->willReturn($task);

        $this->handlerService->taskStart();

        $this->taskService->method('delete')->willReturnSelf();

        $this->handlerService->taskDelete();

        $this->taskService->method('getTask')->willThrowException(new TaskNotFoundException());

        $this->expectException(TaskNotFoundException::class);

        $this->handlerService->getTask();
    }
}