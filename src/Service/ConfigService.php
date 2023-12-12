<?php

namespace Mediashare\Marathon\Service;

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
//        $this->filesystem = new Filesystem();
    }

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
        return (empty($_ENV['APP_ENV']) || strtolower($_ENV['APP_ENV']) !== 'prod');
    }

    public function getLastDateTimeFormat(): string {
        return $this->getLastConfig()->getDateTimeFormat();
    }

    public function getLastTaskDirectory(): string {
        return $this->getLastConfig()->getTaskDirectory();
    }

    public function getLastTaskId(string $taskDirectory): string|null {
        try {
            if ($lastTaskIdByConfig = $this->getLastConfig()->getTaskId()):
                return $lastTaskIdByConfig;
            endif;

            return $this->taskService
                ->setConfig(new Config(taskDirectory: $taskDirectory))
                ->getTasks()?->last()?->getId();

        } catch (\Exception $exception) {

        }

        return null;
    }

    private function setConfigPath(string $configPath): self {
        $this->configPath = $configPath;

        return $this;
    }

    private function getConfigPath(): string|null {
        return $this->configPath;
    }

    private function getLastConfig(): Config {
        return $this->filesystem->exists($this->getConfigPath())
            ? $this->serializerService->read($this->getConfigPath(), Config::class)
            : new Config()
        ;
    }
}