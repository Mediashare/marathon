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
        private TaskService $taskService,
    ) {
        $this->serializerService = new SerializerService();
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws \JsonException
     */
    public function write(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $taskDirectory = null,
        string|null $taskId = null,
    ): self {
        $configPath ? $this->setConfigPath($configPath) : null;

        $this->config = new Config(
            $dateTimeFormat ?? $this->getLastDateTimeFormat(),
            $taskDirectory = $taskDirectory ?? $this->getLastTaskDirectory(),
            $taskId
                ?? $this->getLastTaskId($taskDirectory)
                ?? (new \DateTime())->format('YmdHis')
            ,
        );

        $this->filesystem->dumpFile($this->getConfigPath(), json_encode($this->getConfig()->toArray(), JSON_THROW_ON_ERROR));

        return $this;
    }

    public function getConfig(): Config {
        return $this->config;
    }

    public function isDebug(): bool {
        return ($this->isTest() ||empty($_ENV['APP_ENV']) || strtolower($_ENV['APP_ENV']) !== 'prod');
    }

    public function isTest(): bool {
        return (!empty($_ENV['APP_ENV']) && strtolower($_ENV['APP_ENV']) === 'test');
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getLastDateTimeFormat(): string {
        return $this->getLastConfig()->getDateTimeFormat();
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getLastTaskDirectory(): string {
        return $this->getLastConfig()->getTaskDirectory();
    }

    public function getLastTaskId(string|null $taskDirectory = null): string|null {
        try {
            $lastTaskId = $this->taskService
                ->setConfig(new Config(taskDirectory: $taskDirectory ?? $this->getLastTaskDirectory()))
                ->getTasks()?->last()?->getId()
            ;

            if ($lastTaskId):
                return $lastTaskId;
            endif;

            if ($lastTaskIdByLastConfig = $this->getLastConfig()->getTaskId()):
                return $lastTaskIdByLastConfig;
            endif;
        } catch (\Exception $exception) {}

        return null;
    }

    private function setConfigPath(string $configPath): self {
        $this->configPath = $configPath;

        return $this;
    }

    private function getConfigPath(): string|null {
        return $this->configPath;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    private function getLastConfig(): Config {
        if (!$this->isTest() && $this->filesystem->exists($this->getConfigPath())):
            return $this->serializerService->read($this->getConfigPath(), Config::class);
        elseif (!$this->isTest() && $this->filesystem->exists(Config::CONFIG_PATH)):
            return $this->serializerService->read(Config::CONFIG_PATH, Config::class);
        endif;

        return new Config();
    }
}