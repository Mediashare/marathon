<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TaskCollection;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\StrToTimeException;
use Mediashare\Marathon\Exception\TaskNotFoundException;

class HandlerService {
    private Config $config;
    private Task|null $task;

    public function __construct(
        private ConfigService $configService,
        private TaskService $taskService,
        private CommitService $commitService,
        private SerializerService $serializerService,
    ) {}

    public function setConfig(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $taskDirectory = null,
        string|null $taskId = null,
    ): self {
        $this->config = $this->configService->write(
            $configPath,
            $dateTimeFormat,
            $taskDirectory,
            $taskId,
        )->getConfig();

        return $this;
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @throws \JsonException
     */
    public function updateConfigCurrentTaskId(): self {
        $lastTaskId = $this->configService->getLastTaskId();

        $this->configService->write(
            taskId: $this->getConfig()->getTaskId() === $lastTaskId
                ? null
                : $lastTaskId
            ,
        );

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
        return $this->task
            ?? $this->taskService
                ->setConfig($this->getConfig())
                ->getTask($createItIfNotExist)
            ;
    }

    public function getTasks(): TaskCollection {
        return $this->taskService
            ->setConfig($this->getConfig())
            ->getTasks();
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function start(
        string|false $name = false,
        string|false $duration = false,
    ): self {
        return $this->setTask(
            $this->taskService
                ->setConfig($this->getConfig())
                ->start($name, $duration)
                ->getTask()
        );
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function stop(): self {
        return $this->setTask(
            $this->taskService
                ->setConfig($this->getConfig())
                ->stop()
                ->getTask()
        );
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function archive(): self {
        return $this->setTask(
            $this->taskService
                ->setConfig($this->getConfig())
                ->archive()
                ->getTask()
        );
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function delete(): self {
        $taskService = $this->taskService->setConfig($this->getConfig());

        $this->setTask($taskService->getTask());

        $taskService->delete();

        return $this->setTask(null);
    }

    /**
     * @throws CommitNotFoundException
     * @throws FileNotFoundException
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws StrToTimeException
     */
    public function commit(
        string|false $id = false,
        string|false $message = false,
        string|false $duration = false,
        bool|null $isDelete = false,
    ): self {
        if ($id === false):
            $this->commitService
                ->setTask($this->getTask(createItIfNotExist: true))
                ->create(
                    $message,
                    $duration
                );
        elseif ($isDelete === true):
            $this->commitService
                ->setTask($this->getTask())
                ->delete($id);
        else:
            $this->commitService
                ->setTask($this->getTask())
                ->edit(
                    $id,
                    $message,
                    $duration
                );
        endif;

        return $this->setTask($this->commitService->getTask());
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function write(): self {
        $this->serializerService
            ->writeTask(
                $this->taskService->getTaskFilepath(),
                $this->getTask()
            );

        return $this;
    }
}