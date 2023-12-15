<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TaskService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class ConfigServiceTest extends AbstractTestCase {
    private ConfigService $configService;
    private string $tempConfigPath;

    protected function setUp(): void
    {
        $this->tempConfigPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'config.json';
        $this->configService = new ConfigService(new TaskService(new StepService()));
    }

    /**
     * @throws \JsonException
     */
    public function testWrite(): void {
        $config = $this->configService->write($this->tempConfigPath)->getConfig();

        $this->assertFileExists($this->tempConfigPath);
        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals(Config::DATETIME_FORMAT, $config->getDateTimeFormat());
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastDateTimeFormat(): void {
        // Create a config file with a specific datetime format
        $this->configService->write($this->tempConfigPath, 'Y-m-d H:i:s');
        $lastDateTimeFormat = $this->configService->getLastDateTimeFormat();

        $this->assertEquals('Y-m-d H:i:s', $lastDateTimeFormat);
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastTaskDirectory(): void {
        // Create a config file with a specific task directory
        $this->configService->write($this->tempConfigPath, null, '/path/to/task');
        $lastTaskDirectory = $this->configService->getLastTaskDirectory();

        $this->assertEquals('/path/to/task', $lastTaskDirectory);
    }

    /**
     * @throws \JsonException
     */
    public function testGetLastTaskId(): void {
        // Create a config file with a specific task directory and ID
        $this->configService->write($this->tempConfigPath, null, '/path/to/task', '12345');
        $lastTaskId = $this->configService->getLastTaskId('/path/to/task');

        $this->assertEquals('12345', $lastTaskId);
    }
}