<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Exception\StrToTimeDurationException;

class StepService {
    public function __construct(
        private TimestampService $timestampService,
    ) {}

    public function create(
        int|null $startDate = null,
        int|null $endDate = null,
    ): Step {
        return (new Step())
            ->setStartDate($startDate ?? (new \DateTime())->getTimestamp())
            ->setEndDate($endDate)
        ;
    }

    /**
     * Create step with custom duration
     *
     * @throws StrToTimeDurationException
     * @param int|null $startDate Timestamp of startDate
     * @param string $duration (exemple: '+5minutes', '+2hours', '+1days')
     */
    public function createWithCustomDuration(string $duration, int|null $startDate = null): Step {
        $originalDuration = $duration;
        $duration = $this->timestampService->convert($duration);

        $startDate = $startDate ?? (new \DateTime())->getTimestamp();
        $endDate = strtotime($duration, $startDate);

        if (!$endDate):
            throw new StrToTimeDurationException($originalDuration);
        endif;

        return (new Step())
            ->setStartDate($startDate)
            ->setEndDate($endDate);
    }
}