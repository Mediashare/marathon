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
        // Duration normalizer
        $duration = strtolower($duration);
        $duration = preg_replace('/\s+/', '', $duration);
        // Weeks normalizer
        $duration = str_replace(['weeks', 'week'], 'w', $duration);
        $duration = preg_replace('/(\d+)w/', '$1weeks', $duration);
        // Days normalizer
        $duration = str_replace(['days', 'day'], 'd', $duration);
        $duration = preg_replace('/(\d+)d/', '$1days', $duration);
        // Hours normalizer
        $duration = str_replace(['hours', 'hour'], 'h', $duration);
        $duration = preg_replace('/(\d+)h/', '$1hours', $duration);
        // Seconds normalizer
        $duration = str_replace(['seconds', 'second'], 's', $duration);
        $duration = preg_replace('/(\d+)s/', '$1seconds', $duration);

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