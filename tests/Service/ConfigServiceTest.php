<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TimerService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class ConfigServiceTest extends AbstractTestCase {
    private ConfigService $configService;
    private string $tempConfigPath;

    protected function setUp(): void
    {
        $this->tempConfigPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'config.json';
        $this->configService = new ConfigService(new TimerService(new StepService()));
    }

    public function testWrite(): void {
        $config = $this->configService->write($this->tempConfigPath);

        $this->assertFileExists($this->tempConfigPath);
        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals(Config::DATETIME_FORMAT, $config->getDateTimeFormat());
    }

    public function testGetLastDateTimeFormat(): void {
        // Create a config file with a specific datetime format
        $this->configService->write($this->tempConfigPath, 'Y-m-d H:i:s');
        $lastDateTimeFormat = $this->configService->getLastDateTimeFormat();

        $this->assertEquals('Y-m-d H:i:s', $lastDateTimeFormat);
    }

    public function testGetLastTimerDirectory(): void {
        // Create a config file with a specific timer directory
        $this->configService->write($this->tempConfigPath, null, '/path/to/timer');
        $lastTimerDirectory = $this->configService->getLastTimerDirectory();

        $this->assertEquals('/path/to/timer', $lastTimerDirectory);
    }

    public function testGetLastTimerId(): void {
        // Create a config file with a specific timer directory and ID
        $this->configService->write($this->tempConfigPath, null, '/path/to/timer', '12345');
        $lastTimerId = $this->configService->getLastTimerId('/path/to/timer');

        $this->assertEquals('12345', $lastTimerId);
    }
}