<?php

namespace Mediashare\Marathon\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
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

    public function getStartDateFormated(Config $config): string|null {
        return $this->getStartDate()
            ? (new \DateTime())
                ->setTimestamp($this->getStartDate())
                ->setTimezone($config->getDateTimeZone())
                ->format($config->getDateTimeFormat())
            : null
        ;
    }

    public function getEndDate(): int|null {
        $endDate = null;

        switch (self::class) {
            case Task::class:
                if (
                    $this->getSteps()->last()?->getEndDate() !== null
                    && ($steps = $this
                        ->getSteps()
                        ->filter(static fn (Step $step) => $step->getEndDate())
                    )->count() > 0
                ):
                    $endDate = $steps->last()->getEndDate();
                elseif (($commits = $this->getCommits())->count() > 0):
                    $endDate = $commits->last()->getEndDate();
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

    public function getEndDateFormated(Config $config): string|null {
        return $this->getEndDate()
            ? (new \DateTime())
                ->setTimestamp($this->getEndDate())
                ->setTimezone($config->getDateTimeZone())
                ->format($config->getDateTimeFormat())
            : null
        ;
    }
}