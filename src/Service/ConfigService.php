<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Symfony\Component\Filesystem\Filesystem;
use Mediashare\Marathon\Entity\Config;

class ConfigService {
    private SerializerService $serializerService;
    private Filesystem $filesystem;

    private Config|null $config = null;

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
     * @throws DateTimeZoneException
     */
    public function write(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $dateTimeZone = null,
        string|null $taskDirectory = null,
        string|null $taskId = null,
    ): self {
        $this->config = new Config(
            $configPath ?? $this->getLastConfigPath(),
        $dateTimeFormat ?? $this->getLastDateTimeFormat(),
        $dateTimeZone ?? $this->getLastDateTimeZone()->getName(),
            $taskDirectory = $taskDirectory ?? $this->getLastTaskDirectory(),
            $taskId
                ?? $this->getLastTaskId($taskDirectory)
                ?? (new \DateTime())->format('YmdHis')
            ,
        );

        $this->filesystem->dumpFile($this->config->getConfigPath(), json_encode($this->config->toArray(), JSON_THROW_ON_ERROR));

        return $this;
    }

    public function getConfig(): Config|null {
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
    public function getLastConfigPath(): string {
        return $this->getLastConfig()->getConfigPath();
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
    public function getLastDateTimeZone(): \DateTimeZone {
        return $this->getLastConfig()->getDateTimeZone();
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getLastTaskDirectory(): string {
        return $this->getLastConfig()->getTaskDirectory();
    }

    public function getLastTaskId(string|null $taskDirectory = null, string|null $excludeTaskId = null): string|null {
        try {
            $lastTaskId = $this->taskService
                ->setConfig(new Config(taskDirectory: $taskDirectory ?? $this->getLastTaskDirectory()))
                ->getTasks()
                ?->filter(static fn (Task $task) => !$excludeTaskId || $task->getId() !== $excludeTaskId)
                ?->last()?->getId()
            ;

            if ($lastTaskId):
                return $lastTaskId;
            endif;

            if ($lastTaskIdByLastConfig = $this->getLastConfig()->getTaskId()):
                if (!$excludeTaskId || $lastTaskIdByLastConfig !== $excludeTaskId):
                    return $lastTaskIdByLastConfig;
                endif;
            endif;
        } catch (\Exception $exception) {}

        return null;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    private function getLastConfig(): Config {
        if (
            $this->getConfig() instanceof Config
            && !$this->isTest()
            && $this->filesystem->exists($configPath = $this->getConfig()->getConfigPath())
        ):
            return $this->serializerService->read($configPath, Config::class);
        elseif (!$this->isTest() && $this->filesystem->exists($configPath = Config::CONFIG_PATH)):
            return $this->serializerService->read($configPath, Config::class);
        endif;

        return new Config();
    }
}