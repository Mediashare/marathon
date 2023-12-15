<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TaskCollection;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TaskNotFoundException;

class HandlerService {
    private Config $config;
    private Task $task;

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
    public function updateCurrentTrackingId(): self {
        $lastTaskId = $this->configService->getLastTaskId(
            $this->config->getTaskDirectory(),
        );

        $this->configService->write(
            taskId: $this->getConfig()->getTaskId() === $lastTaskId
                ? (new \DateTime())->format('YmdHis')
                : $lastTaskId
            ,
        );

        return $this;
    }

    private function setTask(Task $task): self {
        $this->task = $task;

        return $this;
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTask(): Task {
        return $this->task
            ?? $this->taskService
                ->setConfig($this->getConfig())
                ->getTask()
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

        $this->setTask($taskService->getTask(createItIfNotExist: false));

        $taskService->delete();

        return $this;
    }

    /**
     * @throws CommitNotFoundException
     * @throws FileNotFoundException
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     */
    public function commit(
        string|false $id = false,
        string|false $message = false,
        string|false $duration = false,
        bool|null $isDelete = false,
    ): self {
        if ($id === false):
            $this->commitService
                ->setTask($this->getTask())
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