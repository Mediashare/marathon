<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\MissingParameterException;
use Mediashare\Marathon\Exception\StrToTimeDurationException;

class CommitService {
    private Task $task;

    public function __construct(
        private StepService $stepService,
    ) { }

    public function setTask(Task $task): self {
        $this->task = $task;

        return $this;
    }

    public function getTask(): Task {
        return $this->task;
    }

    /**
     * @throws StrToTimeDurationException
     */
    public function create(
        string|null $message = null,
        string|null $duration = null,
    ): self {
        $task = $this->getTask();

        $commit = (new Commit())
            ->setId((new \DateTime())->format('YmdHis'))
            ->setMessage($message);

        if ($duration):
            $commit->addStep(
                $this->stepService->createWithCustomDuration(
                    $duration,
                    ($lastStep = $task->getSteps()?->last())?->getEndDate()
                        ? $lastStep->getStartDate()
                        : null
                )
            );
        elseif (($steps = $task->getSteps())?->count() > 0):
            if (($lastStep = $steps->last())->getEndDate() === null):
                $task->getSteps()->offsetSet(
                    $task->getSteps()->getKey($lastStep),
                    $lastStep->setEndDate((new \DateTime())->getTimestamp()),
                );
            endif;

            $commit->setSteps($task->getSteps());
        else:
            $commit->addStep($this->stepService->create());
        endif;

        $task
            ->addCommit($commit)
            ->getSteps()->clear();

        if ($task->isRun()):
            $this
                ->getTask()
                ->addStep(
                    $this->stepService->create()
                );
        endif;

        return $this->setTask($task);
    }

    /**
     * @throws CommitNotFoundException
     * @throws StrToTimeDurationException
     * @throws MissingParameterException
     */
    public function edit(
        string $id,
        string|false $message = false,
        string|false $duration = false,
    ): self {
        if ($message === false && $duration === false):
            throw new MissingParameterException();
        endif;

        $task = $this->getTask();

        if (($commit = $task
                ->getCommits()
                ->findOneBy(
                    static fn (Commit $commit) => $commit->getId() === $id
                )) === null
        ) {
            throw new CommitNotFoundException();
        }

        $key = $task->getCommits()->getKey($commit);

        if ($message !== false):
            $task
                ->getCommits()
                ->offsetSet($key, $commit->setMessage($message));
        endif;

        if ($duration !== false):
            $startDate = $commit->getStartDate() ?? (new \DateTime())->getTimestamp();
            $commit->getSteps()->clear();
            $task
                ->getCommits()
                ->offsetSet(
                    $key,
                    $commit
                        ->addStep(
                            $this
                                ->stepService
                                ->createWithCustomDuration(
                                    $duration,
                                    $startDate,
                                )
                        )
                );
        endif;

        return $this->setTask($task);
    }

    /**
     * @throws CommitNotFoundException
     */
    public function delete(
        string $id,
    ): self {
        $task = $this->getTask();
        if (($commit = $task->getCommits()->findOneBy(static fn (Commit $commit) => $commit->getId() === $id)) ===
            null):
            throw new CommitNotFoundException();
        endif;

        $task->getCommits()->remove($commit);

        return $this->setTask($task);
    }
}