<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TaskCollection;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TaskNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class TaskService {
    private SerializerService $serializerService;
    private Filesystem $filesystem;

    private Config $config;
    private Task|null $task = null;

    public function __construct(
        private readonly StepService $stepService,
    ) {
        $this->serializerService = new SerializerService();
        $this->filesystem = new Filesystem();
    }

    public function setConfig(Config $config): self {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTasks(): TaskCollection {
        $taskCollection = new TaskCollection();
        foreach (glob($this->config->getTaskDirectory() . '/*') as $filepath):
            $taskCollection->add($this->serializerService->read($filepath, Task::class));
        endforeach;

        return $taskCollection;
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTask(bool $createItIfNotExist = true): Task {
        if ($this->task instanceof Task):
            return $this->task;
        endif;

        $taskExist = $this->filesystem->exists($filepath = $this->getTaskFilepath());
        if (!$taskExist && $createItIfNotExist):
            return $this->create()->getTask();
        elseif (!$taskExist):
            throw new TaskNotFoundException();
        endif;

        return $this
            ->setTask($this->serializerService->read($filepath, Task::class))
            ->getTask();
    }

    public function setTask(Task|null $task = null): self {
        $this->task = $task;

        return $this;
    }

    /**
     * @throws TaskNotFoundException
     */
    public function create(array $data = []): self {
        /** @var Task $task */
        $task = $this->serializerService->arrayToEntity($data, Task::class);

        if (!$task->getId()):
            $task->setId($this->config->getTaskId() ?? (new \DateTime())->format('YmdHis'));
        endif;

        if ($task->isRun() && !$task->getSteps()?->last()?->getEndDate()):
            $task->addStep($this->stepService->create());
        endif;

        $this->serializerService->writeTask($this->getTaskFilepath(), $task);

        return $this->setTask($task);
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     */
    public function start(
        string|false $name = false,
        string|false $duration = false,
    ): self {
        $task = $this->getTask()
            ->setRun(true)
            ->setName($name !== false ? $name : $this->getTask()->getName());

        if ($duration):
            $firstStep = $task->getSteps()->first();
            $task->getSteps()->clear();
            $task->addStep($this
                ->stepService
                ->createWithCustomDuration(
                    $duration,
                    $firstStep?->getStartDate(),
                )
            );
        endif;

        if (!$task->getStartDate() || !($lastStep = $task->getSteps()?->last()) || $lastStep->getEndDate()):
            $task
                ->addStep(
                    $this->stepService->create()
                );
        endif;

        return $this->setTask($task);
    }

    /**
     * @throws TaskNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function stop(): self {
        $task = $this
            ->getTask(createItIfNotExist: false)
            ->setRun(false);

        if (($lastStep = $task->getSteps()?->last()) && !$lastStep->getEndDate()):
            $task
                ->getSteps()
                ->offsetSet(
                    $task->getSteps()->getKey($lastStep),
                    $lastStep->setEndDate((new \DateTime())->getTimestamp()),
                );
        endif;

        return $this->setTask($task);
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     */
    public function archive(): self {
        $this
            ->stop()
            ->getTask(createItIfNotExist: false)
            ->setArchived(true);

        return $this;
    }

    /**
     * @throws TaskNotFoundException
     */
    public function delete(): self {
        $this->filesystem
            ->remove($this->getTaskFilepath())
        ;

        return $this->setTask(null);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function getTaskFilepath(): string {
        if (!$this->getConfig()->getTaskId()):
            throw new TaskNotFoundException();
        endif;

        return $this->getConfig()->getTaskDirectory().DIRECTORY_SEPARATOR. $this->getConfig()->getTaskId().'.json';
    }
}