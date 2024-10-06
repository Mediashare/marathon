<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Symfony\Component\Filesystem\Filesystem;
use Mediashare\Marathon\Entity\Config;

class ConfigService {
    private Config|null $config = null;

    public function __construct(
        private readonly TaskService $taskService,
        private readonly SerializerService $serializerService,
        private readonly Filesystem $filesystem,
    ) { }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws DateTimeZoneException
     * @throws \JsonException
     */
    public function setConfig(
        string|false $configPath = false,
        string|false $dateTimeFormat = false,
        string|false $dateTimeZone = false,
        string|false $taskDirectory = false,
        string|false $editor = false,
        string|null|false $taskId = null,
    ): self {
        if ($configPath && $configPath !== $this->getLastConfigPath()):
            $this->writeLastConfigPathIntoMainConfig($configPath);
        endif;

        $this->config = new Config(
            $configPath = $configPath ?: $this->getLastConfigPath(),
            $dateTimeFormat ?: $this->getLastDateTimeFormat(),
            $dateTimeZone ?: $this->getLastDateTimeZone()->getName(),
            $taskDirectory ?: $this->getLastTaskDirectory(),
            $editor ?: $this->getLastEditor(),
            $taskId === false
                ? null
                : ($taskId
                    ?: $this->getLastTaskIdByConfig(configPath: $configPath, taskDirectory: $taskDirectory)
                    ?: $this->getLastTaskIdByDirectory(configPath: $configPath, taskDirectory: $taskDirectory)
                )
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
    public function write(string|null $configPath = null): self {
        $this
            ->filesystem
            ->dumpFile(
                $configPath ?? $this->getConfig()?->getConfigPath() ?? $this->getLastConfigPath(),
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

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getLastEditor(): string {
        return $this->getLastConfig()->getEditor();
    }

    public function getLastTaskIdByConfig(
        string|null $configPath = null,
        string|null $taskDirectory = null,
        string|null $excludeTaskId = null,
    ): string|null {
        try {
            if (
                ($lastTaskIdByLastConfig = $this->getLastConfig($configPath)->getTaskId())
                && (
                    !$excludeTaskId
                    || $lastTaskIdByLastConfig !== $excludeTaskId
                )
            ):
                return $this
                    ->taskService
                    ->setConfig(
                        ($lastConfig = $this->getLastConfig($configPath))
                            ->setTaskDirectory($taskDirectory ?? $lastConfig->getTaskDirectory())
                    )->getTask()
                    ->getId();
            endif;
        } catch (\Exception $exception) {}

        return null;
    }

    public function getLastTaskIdByDirectory(
        string|null $configPath = null,
        string|null $taskDirectory = null,
        string|null $excludeTaskId = null,
    ): string|null {
        try {
            $lastTaskId = $this->taskService
                ->setConfig(
                    $taskDirectory
                        ? $this->getLastConfig($configPath)->setTaskDirectory($taskDirectory)
                        : $this->getLastConfig($configPath)
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
                })?->lastUpdated()?->getId();


            if ($lastTaskId):
                return $lastTaskId;
            endif;
        } catch (\Exception $exception) {}

        return null;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    private function getLastConfig(string|null $configPath = null): Config {
        if ($configPath !== null && $this->filesystem->exists($configPath)):
            return $this->serializerService->read($configPath, Config::class);
        elseif (
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

    /**
     * If new configPath then write this into main config
     *
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws \JsonException
     */
    private function writeLastConfigPathIntoMainConfig(string $configPath): void
    {
        $this->config = $this->getLastConfig(Config::CONFIG_PATH)
            ->setConfigPath($configPath)
        ;
        $this->write(Config::CONFIG_PATH);
    }
}