<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TaskCollection;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\CommandMissingLeastOnceOptionException;
use Mediashare\Marathon\Exception\StrToTimeDurationException;
use Mediashare\Marathon\Exception\TaskNotFoundException;

class HandlerService {
    private Config $config;
    private Task|null $task = null;

    public function __construct(
        public readonly ConfigService $configService,
        private readonly TaskService $taskService,
        private readonly CommitService $commitService,
        private readonly SerializerService $serializerService,
    ) {}

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     * @throws \JsonException
     * @throws DateTimeZoneException
     */
    public function writeConfig(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $dateTimeZone = null,
        string|null $taskDirectory = null,
        string|null $taskId = null,
    ): self {
        $this->config = $this->configService->setConfig(
            $configPath,
            $dateTimeFormat,
            $dateTimeZone,
            $taskDirectory,
            $taskId,
        )->write()->getConfig();

        return $this;
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @throws \JsonException
     */
    public function updateTaskIdInConfig(): self {
        $this->configService->setConfig(
            taskId: false
        )->write();

        return $this;
    }

    private function setTask(Task|null $task): self {
        $this->task = $task;

        return $this;
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTask(bool $createItIfNotExist = false): Task {
        if ($this->task):
            return $this->task;
        endif;

        return $this->taskService
            ->setConfig($this->getConfig())
            ->getTask($createItIfNotExist);
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTasks(): TaskCollection {
        return $this->taskService
            ->setConfig($this->getConfig())
            ->getTasks();
    }

    /**
     * @throws TaskNotFoundException
     * @throws StrToTimeDurationException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function taskStart(
        string|false $name = false,
        string|false $duration = false,
    ): self {
        return $this->setTask(
            $this->taskService
                ->setConfig($this->getConfig())
                ->start($name, $duration)
                ->getTask()
        )->writeTask();
    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function taskStop(): self {
        return $this->setTask(
            $this->taskService
                ->setConfig($this->getConfig())
                ->stop(createItIfNotExist: true)
                ->getTask()
        )->writeTask();
    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function taskArchive(): self {
        return $this->setTask(
            $this->taskService
                ->setConfig($this->getConfig())
                ->archive()
                ->getTask()
        )->writeTask();
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function taskDelete(): self {
        $taskService = $this->taskService->setConfig($this->getConfig());

        $this->setTask($taskService->getTask());

        $taskService->delete();

        return $this->setTask(null);
    }

    /**
     * @throws TaskNotFoundException
     * @throws StrToTimeDurationException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function commit(
        string|null $message = null,
        string|null $duration = null,
    ): self {
        $this->commitService
            ->setTask($this->getTask(createItIfNotExist: true))
            ->create(
                $message,
                $duration,
            );

        return $this->setTask($this->commitService->getTask())->writeTask();
    }

    /**
     * @throws TaskNotFoundException
     * @throws CommitNotFoundException
     * @throws StrToTimeDurationException
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws CommandMissingLeastOnceOptionException
     */
    public function commitEdit(
        string $commitId,
        string|false $message = false,
        string|false $duration = false,
    ): self {
        $this->commitService
            ->setTask($this->getTask())
            ->edit(
                $commitId,
                $message,
                $duration
            );

        return $this->setTask($this->commitService->getTask())->writeTask();
    }

    /**
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     * @throws CommitNotFoundException
     * @throws JsonDecodeException
     */
    public function commitDelete(
        string $commitId,
    ): self {
        $this->commitService
            ->setTask($this->getTask())
            ->delete($commitId);

        return $this->setTask($this->commitService->getTask())->writeTask();
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    private function writeTask(): self {
        $this->serializerService
            ->writeTask(
                $this->taskService->getTaskFilepath(),
                $this->getTask()
            );

        return $this;
    }
}