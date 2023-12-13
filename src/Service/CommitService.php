<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Timer;
use Mediashare\Marathon\Exception\CommitNotFoundException;

class CommitService {
    private Timer $timer;

    public function __construct(
        private StepService $stepService,
    ) { }

    public function setTimer(Timer $timer): self {
        $this->timer = $timer;

        return $this;
    }

    public function getTimer(): Timer {
        return $this->timer;
    }

    public function create(
        ?string $message = null,
        ?string $duration = null
    ): self {
        $timer = $this->getTimer();

        $commit = (new Commit())
            ->setId((new \DateTime())->format('YmdHis'))
            ->setMessage($message);

        if ($duration):
            $commit->addStep(
                $this->stepService->createWithCustomDuration(
                    $duration,
                    ($lastStep = $timer->getSteps()?->last())?->getEndDate()
                        ? $lastStep->getStartDate()
                        : null
                )
            );
        elseif (($steps = $timer->getSteps())->count() > 0):
            /** @var Step $step */
            foreach ($steps as $step):
                if (!$step->getEndDate()):
                    $step->setEndDate((new \DateTime())->getTimestamp());
                endif;
                $commit->addStep($step);
            endforeach;
        else:
            $commit
                ->addStep((new Step())
                ->setStartDate($dateTime = (new \DateTime())->getTimestamp())
                ->setEndDate($dateTime));
        endif;

        $timer
            ->addCommit($commit)
            ->getSteps()->clear();

        if ($timer->isRun()):
            $this
                ->getTimer()
                ->addStep(
                    $this->stepService->create()
                );
        endif;

        return $this->setTimer($timer);
    }

    /**
     * @throws CommitNotFoundException
     */
    public function edit(
        string $id,
        string|false $message = false,
        string|false $duration = false,
    ): self {
        $timer = $this->getTimer();

        if (($commit = $timer
                ->getCommits()
                ->findOneBy(
                    static fn (Commit $commit) => $commit->getId() === $id
                )) === null
        ) {
            throw new CommitNotFoundException();
        }

        $key = $timer->getCommits()->getKey($commit);

        if ($message !== false):
            $timer
                ->getCommits()
                ->offsetSet($key, $commit->setMessage($message));
        endif;

        if ($duration !== false):
            $startDate = $commit->getStartDate() ?? (new \DateTime())->getTimestamp();
            $commit->getSteps()->clear();
            $timer
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

        return $this->setTimer($timer);
    }

    /**
     * @throws CommitNotFoundException
     */
    public function delete(
        string $id,
    ): self {
        $timer = $this->getTimer();
        if (($commit = $timer->getCommits()->findOneBy(static fn (Commit $commit) => $commit->getId() === $id)) ===
            null):
            throw new CommitNotFoundException();
        endif;

        $timer->getCommits()->remove($commit);

        return $this->setTimer($timer);
    }
}