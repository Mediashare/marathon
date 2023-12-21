<?php

namespace Mediashare\Marathon\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Task;

trait EntityDateTimeTrait {
    public function getStartDate(): int|null {
        switch (self::class) {
            case Task::class:
                $startDate =
                    $this->getCommits()?->first()?->getStartDate()
                    ?? $this->getSteps()?->first()?->getStartDate()
                ;
                break;
            case Commit::class:
                $startDate = $this->getSteps()?->first()?->getStartDate();
                break;
            case Step::class:
                $startDate = $this->startDate;
                break;
        }

        return $startDate;
    }

    public function getStartDateFormated(string $format): string|null {
        return $this->getStartDate()
            ? (new \DateTime())->setTimestamp($this->getStartDate())->format($format)
            : null
        ;
    }

    public function getEndDate(): int|null {
        $endDate = null;

        switch (self::class) {
            case Task::class:
                if (($commits = $this->getCommits())->count() > 0):
                    $endDate = $commits->last()?->getEndDate();
                elseif (($steps = $this->getSteps()->filter(static fn (Step $step) => $step->getEndDate()))->count() > 1):
                    $endDate = $steps->last()->getEndDate();
                endif;
                break;
            case Commit::class:
                $endDate = $this->getSteps()?->last()?->getEndDate();
                break;
            case Step::class:
                $endDate = $this->endDate;
                break;
        }

        return $endDate;
    }

    public function getEndDateFormated(string $format): string|null {
        return $this->getEndDate()
            ? (new \DateTime())->setTimestamp($this->getEndDate())->format($format)
            : null
        ;
    }
}