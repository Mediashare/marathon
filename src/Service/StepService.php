<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Exception\StrToTimeException;

class StepService {
    public function create(
        string|null $startDate = null,
        string|null $endDate = null,
    ): Step {
        return (new Step())
            ->setStartDate($startDate ?? (new \DateTime())->getTimestamp())
            ->setEndDate($endDate)
        ;
    }

    /**
     * Create step with custom duration
     *
     * @throws StrToTimeException
     * @param int|null $startDate Timestamp of startDate
     * @param string $duration (exemple: '+5minutes', '+2hours', '+1days')
     */
    public function createWithCustomDuration(string $duration, int|null $startDate = null): Step {
        // $duration normalizer
        $duration = strtolower($duration);
        $duration = str_replace(['hour', 'hours'], 'h', $duration);
        $duration = preg_replace('/(\d+)h/', '$1hours', $duration);

        $startDate = $startDate ?? (new \DateTime())->getTimestamp();
        $endDate = strtotime($duration, $startDate);

        if (!$endDate):
            throw new StrToTimeException();
        endif;

        return (new Step())
            ->setStartDate($startDate)
            ->setEndDate($endDate);
    }
}