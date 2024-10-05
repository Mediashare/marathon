<?php

namespace Mediashare\Marathon\Trait;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Task;

trait EntityDurationTrait {
    /**
     * Convert seconds to "d H:i:s" format
     *
     * @param bool|null $onlyCurrentSteps If true, consider only non-committed seconds.
     * @param int $totalSeconds Additional seconds to be added to the total duration.
     *
     * @return string Formatted duration string.
     */
    public function getDuration(bool|null $onlyCurrentSteps = false, int $totalSeconds = 0): string {
        return trim((((
                $days = floor(($seconds = $this->getSeconds($onlyCurrentSteps) + $totalSeconds) / 86400)) > 0)
                    ? "{$days}d "
                    : ""
            )
            . (($hours = floor($seconds / 3600) % 24) ? $hours . "h " : "")
            . (($minutes = floor(($seconds % 3600) / 60)) ? $minutes . "min " : "")
            . (($seconds = $seconds % 60) ? $seconds ."s" : "")
        );
    }

    public function getSeconds(bool|null $onlyCurrentSteps = false): int {
        switch (self::class) {
            case Task::class:
                $seconds = array_sum(
                    array_merge(
                        !$onlyCurrentSteps
                            ? $this
                                ->getCommits()
                                ->map(
                                    static fn (Commit $commit) => $commit->getSeconds()
                                )->toArray()
                            : [],
                        $this
                            ->getSteps()
                            ->map(static fn (Step $step) => $step->getSeconds())
                            ->toArray(),
                    )
                );
                break;
            case Commit::class:
                $seconds = array_sum(
                    $this
                        ->getSteps()
                        ->map(static fn (Step $step) => $step->getSeconds())
                        ->toArray()
                );
                break;
            case Step::class:
                $seconds = ($this->getEndDate() ?? (new \DateTime())->getTimestamp()) - $this->getStartDate();
                break;
            default: $seconds = 0;
        }

        return $seconds;
    }
}