<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\TaskService;

class ConfigServiceTest extends AbstractServiceTestCase {
    private ConfigService $configService;

    public function setUp(): void {
        parent::setUp();

        $this->configService = new ConfigService($this->createMock(TaskService::class));
    }

    /**
     * @throws \JsonException
     */
    public function testWrite(): void {
        $config = $this->configService->write(
            configPath: $this->configPath,
            dateTimeFormat: 'm/d/Y H:i:s',
            taskDirectory: $taskDirectory = $this->marathonDirectory . DIRECTORY_SEPARATOR . 'tasks',
            taskId: $taskId = 'taskId',
        )->getConfig();

        $this->assertFileExists($this->configPath);
        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('m/d/Y H:i:s', $config->getDateTimeFormat());
        $this->assertEquals($taskDirectory, $config->getTaskDirectory());
        $this->assertEquals($taskId, $config->getTaskId());
    }

    public function testIsDebug(): void {
        $this->assertTrue($this->configService->isDebug());
    }

    public function testIsTest(): void {
        $this->assertTrue($this->configService->isTest());
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastDateTimeFormat(): void {
        $lastDateTimeFormat = $this->configService->getLastDateTimeFormat();

        $this->assertEquals(Config::DATETIME_FORMAT, $lastDateTimeFormat);
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastTaskDirectory(): void {
        $lastTaskDirectory = $this->configService->getLastTaskDirectory();

        $this->assertEquals(Config::TASKS_DIRECTORY, $lastTaskDirectory);
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastTaskId(): void {
        $lastTaskId = $this->configService->getLastTaskId(taskDirectory: '/path/to/task');

        $this->assertNotEquals('12345', $lastTaskId);
    }
}