<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Exception\DateTimeZoneException;
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
            dateTimeZone: 'Europe/London',
            taskDirectory: $this->taskDirectory,
            taskId: $taskId = 'taskId',
        )->getConfig();

        $this->assertFileExists($this->configPath);
        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('m/d/Y H:i:s', $config->getDateTimeFormat());
        $this->assertEquals(timezone_open('Europe/London'), $config->getDateTimeZone());
        $this->assertEquals($this->taskDirectory, $config->getTaskDirectory());
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
    public function testGetLastDateTimeZone(): void {
        $lastDateTimeZone = $this->configService->getLastDateTimeZone();

        $this->assertEquals(timezone_open(Config::DATETIME_ZONE), $lastDateTimeZone);
    }

    /**
     * @throws \JsonException
     */
    public function testGetDateTimeZoneEuropeParis(): void {
        $config = $this->configService->write(dateTimeZone: $timezone = 'europe/paris')->getConfig();

        $this->assertEquals(timezone_open($timezone), $config->getDateTimeZone());
    }

    /**
     * @throws \JsonException
     */
    public function testGetDateTimeZoneLondonFail(): void {
        $this->expectException(DateTimeZoneException::class);
        $this->configService->write(dateTimeZone: 'london');
    }

    /**
     * @throws \JsonException
     */
    public function testSetDateTimeZoneFail(): void {
        $this->expectException(DateTimeZoneException::class);
        $this->configService->write(dateTimeZone: 'Gallia/Lugdunum');
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
        $lastTaskId = $this->configService->getLastTaskId(taskDirectory: $this->taskDirectory);

        $this->assertNotEquals('12345', $lastTaskId);
    }
}