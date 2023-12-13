<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TimerService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class ConfigServiceTest extends AbstractTestCase {
    private ConfigService $configService;
    private string $tempConfigPath;

    protected function setUp(): void {
        $this->tempConfigPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'config.json';
        $this->configService = new ConfigService(new TimerService(new StepService()));
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
    public function testGetLastTimerDirectory(): void {
        // Create a config file with a specific timer directory
        $this->configService->write($this->tempConfigPath, null, '/path/to/timer');
        $lastTimerDirectory = $this->configService->getLastTimerDirectory();

        $this->assertEquals('/path/to/timer', $lastTimerDirectory);
    }

    /**
     * @throws JsonDecodeException
     * @throws \JsonException
     * @throws FileNotFoundException
     */
    public function testGetLastTimerId(): void {
        // Create a config file with a specific timer directory and ID
        $this->configService->write($this->tempConfigPath, null, '/path/to/timer', '12345');
        $lastTimerId = $this->configService->getLastTimerId('/path/to/timer');

        $this->assertEquals('12345', $lastTimerId);
    }
}