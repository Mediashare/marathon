<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Exception\DurationStrToTimeException;

class StepService {
    public function __construct(
        private TimestampService $timestampService,
    ) {}

    public function create(
        int|null $startDate = null,
        int|null $endDate = null,
    ): Step {
        return (new Step())
            ->setStartDate($startDate = $startDate ?? (new \DateTime())->getTimestamp())
            ->setSeconds($endDate ? $endDate - $startDate : null)
        ;
    }

    /**
     * Create step with custom duration
     *
     * @throws DurationStrToTimeException
     * @param int|null $startDate Timestamp of startDate
     * @param string $duration (exemple: '+5minutes', '+2hours', '+1days')
     */
    public function createWithCustomDuration(string $duration, int|null $startDate = null): Step {
        $originalDuration = $duration;
        $duration = $this->timestampService->convert($duration);

        $startDate = $startDate ?? (new \DateTime())->getTimestamp();
        $endDate = strtotime($duration, $startDate);

        if (!$endDate):
            throw new DurationStrToTimeException($originalDuration);
        endif;

        return (new Step())
            ->setStartDate($startDate)
            ->setSeconds($endDate - $startDate)
        ;
    }
}