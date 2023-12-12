<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Symfony\Component\Filesystem\Filesystem;
use Mediashare\Marathon\Entity\Config;

class ConfigService {
    private SerializerService $serializerService;
    private Filesystem $filesystem;

    private string $configPath = Config::CONFIG_PATH;
    private Config $config;

    public function __construct(
        private TimerService $timerService,
    ) {
        $this->serializerService = new SerializerService();
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws \JsonException
     */
    public function write(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $timerDirectory = null,
        string|null $timerId = null,
    ): self {
        $configPath ? $this->setConfigPath($configPath) : null;

        $config = new Config(
            $dateTimeFormat ?? $this->getLastDateTimeFormat(),
            $timerDirectory = $timerDirectory ?? $this->getLastTimerDirectory(),
            $timerId
                ?? $this->getLastTimerId($timerDirectory)
                ?? (new \DateTime())->format('YmdHis')
            ,
        );

        $this->filesystem->dumpFile($this->getConfigPath(), json_encode($config->toArray(), JSON_THROW_ON_ERROR));

        return $this->setConfig($config);
    }

    public function getConfig(): Config {
        return $this->config;
    }

    public function isDebug(): bool {
        return (empty($_ENV['APP_ENV']) || strtolower($_ENV['APP_ENV']) !== 'prod');
    }

    public function getLastDateTimeFormat(): string {
        return $this->getLastConfig()->getDateTimeFormat();
    }

    public function getLastTimerDirectory(): string {
        return $this->getLastConfig()->getTimerDirectory();
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getLastTimerId(string $timerDirectory): string|null {
        if ($lastTimerIdByConfig = $this->getLastConfig()->getTimerId()):
            return $lastTimerIdByConfig;
        endif;

        return $this->timerService
            ->setConfig(new Config(timerDirectory: $timerDirectory))
            ->getTimers()?->last()?->getId();
    }

    private function setConfigPath(string $configPath): self {
        $this->configPath = $configPath;

        return $this;
    }

    private function getConfigPath(): string {
        return $this->configPath;
    }

    private function setConfig(Config $config): self {
        $this->config = $config;

        return $this;
    }

    private function getLastConfig(): Config {
        return $this->filesystem->exists($this->getConfigPath())
            ? $this->serializerService->read($this->getConfigPath(), Config::class)
            : new Config()
        ;
    }
}