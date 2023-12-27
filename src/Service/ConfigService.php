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
        private readonly TaskService $taskService,
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
    public function setConfig(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $dateTimeZone = null,
        string|null $taskDirectory = null,
        string|false|null $taskId = null,
    ): self {
        $this->config = new Config(
            $configPath ?? $this->getLastConfigPath(),
        $dateTimeFormat ?? $this->getLastDateTimeFormat(),
        $dateTimeZone ?? $this->getLastDateTimeZone()->getName(),
            $taskDirectory = $taskDirectory ?? $this->getLastTaskDirectory(),
            $taskId === false
                ? null
                : $taskId ?? $this->getLastTaskId(taskDirectory: $taskDirectory)
            ,
        );

        return $this;
    }

    public function getConfig(): Config|null {
        return $this->config;
    }

    /**
     * @throws JsonDecodeException
     * @throws \JsonException
     * @throws FileNotFoundException
     */
    public function write(): self {
        $this
            ->filesystem
            ->dumpFile(
                $this->getConfig()?->getConfigPath() ?? $this->getLastConfig()->getConfigPath(),
                json_encode(
                    $this->getConfig()?->toArray() ?? $this->getLastConfig()->toArray(),
                    JSON_THROW_ON_ERROR
                )
            );

        return $this;
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

    public function getLastTaskId(
        string|null $taskDirectory = null,
        string|null $excludeTaskId = null,
    ): string|null {
        try {
            $lastTaskId = $this->taskService
                ->setConfig(
                    $taskDirectory
                        ? $this->getLastConfig()->setTaskDirectory($taskDirectory)
                        : $this->getLastConfig()
                )->getTasks()
                ?->filter(static function (Task $task) use ($excludeTaskId) {
                    $display = true;
                    if ($task->getId() === $excludeTaskId):
                        $display = false;
                    endif;

                    if ($task->isArchived() === true):
                        $display = false;
                    endif;

                    return $display;
                })?->last()?->getId();


            if ($lastTaskId):
                return $lastTaskId;
            endif;

            if (
                ($lastTaskIdByLastConfig = $this->getLastConfig()->getTaskId())
                && (
                    !$excludeTaskId
                    || $lastTaskIdByLastConfig !== $excludeTaskId
                )
            ):
                $task = $this
                    ->taskService
                    ->setConfig($this->getLastConfig())
                    ->getTask();
                if ($task->isArchived() === false):
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