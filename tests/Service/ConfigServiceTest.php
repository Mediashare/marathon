<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\SerializerService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TaskService;
use Mediashare\Marathon\Service\TimestampService;
use Symfony\Component\Filesystem\Filesystem;

class ConfigServiceTest extends AbstractServiceTestCase {
    private ConfigService $configService;

    public function setUp(): void {
        parent::setUp();

        $this->configService = new ConfigService(
            new TaskService(
                new StepService($timestampService = new TimestampService()),
                $timestampService,
                $serializer = new SerializerService($filesystem = new Filesystem()),
                $filesystem,
            ), $serializer, $filesystem,
        );
    }

    /**
     * @throws \JsonException
     */
    public function testWrite(): void {
        $config = $this->configService->setConfig(
            configPath: $this->configPath,
            dateTimeFormat: 'm/d/Y H:i:s',
            dateTimeZone: 'Europe/London',
            taskDirectory: $this->taskDirectory,
            taskId: $taskId = 'taskId',
        )->write()->getConfig();

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
        $config = $this->configService->setConfig(dateTimeZone: $timezone = 'europe/paris')->getConfig();

        $this->assertEquals(timezone_open($timezone), $config->getDateTimeZone());
    }

    /**
     * @throws \JsonException
     */
    public function testGetDateTimeZoneLondonFail(): void {
        $this->expectException(DateTimeZoneException::class);
        $this->configService->setConfig(dateTimeZone: 'london');
    }

    /**
     * @throws \JsonException
     */
    public function testSetDateTimeZoneFail(): void {
        $this->expectException(DateTimeZoneException::class);
        $this->configService->setConfig(dateTimeZone: 'Gallia/Lugdunum');
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
    public function testGetLastTaskIdByDirectory(): void {
        $lastTaskId = $this->configService->getLastTaskIdByDirectory(taskDirectory: $this->taskDirectory);

        $this->assertNotEquals('12345', $lastTaskId);
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastTaskIdByConfig(): void {
        $lastTaskId = $this->configService->getLastTaskIdByConfig(taskDirectory: $this->taskDirectory);

        $this->assertNotEquals('12345', $lastTaskId);
    }
}