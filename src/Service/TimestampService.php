<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Task;

class TimestampService {
    /**
     * @param string $duration exemples: 10min, 1d, 1 day 10 minutes, 1h, 2 hours, -1hour
     * @return string timestamp
     */
    public function convert(string $duration): string {
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

        return $duration;
    }

    public function uglyRemainingDateTime(Task $task): string
    {
        $remaining = $task->getRemaining();
        $duration = $task->getSeconds();
        $running = $task->isRun();

        if ($remaining === null) {
            return "";
        }

        $startDate = (new \DateTime())->setTimestamp($duration);
        $endDate = (new \DateTime())->setTimestamp($remaining);

        $interval = $startDate->diff($endDate);

        $prefix = $startDate > $endDate ? "+" : "";
        $color = $startDate > $endDate
            ? ($running ? "red-blink" : "red")
            : ($running ? "green-blink" : "green")
        ;

        $formats = [
            'y' => "%yy %mm %dd %hh %imin %ss",
            'm' => "%mm %dd %hh %imin %ss",
            'd' => "%dd %hh %imin %ss",
            'h' => "%hh %imin %ss",
            'i' => "%imin %ss",
            's' => "%ss"
        ];

        foreach ($formats as $property => $format) {
            if ($interval->$property > 0) {
                return $interval->format("<{$color}>{$prefix}{$format}</{$color}>");
            }
        }

        return $interval->format("<{$color}>{$prefix}{$formats['s']}</{$color}>");
    }
}