<?php
namespace Mediashare\Marathon\Entity;

use Mediashare\Marathon\Trait\EntityDateTimeTrait;
use Mediashare\Marathon\Trait\EntityDurationTrait;
use Mediashare\Marathon\Trait\EntityUnserializerTrait;

class Step {
    use EntityDateTimeTrait;
    use EntityDurationTrait;
    use EntityUnserializerTrait;

    private string $startDate;
    /** @deprecated 0.1.3 */
    private string|null $endDate = null;
    private int|null $seconds = null;

    public function setStartDate(string $startDate): self {
        $this->startDate = $startDate;

        return $this;
    }

    /** @deprecated 0.1.3 */
    public function setEndDate(string|null $endDate = null): self {
        $this->endDate = $endDate;

        return $this;
    }

    public function setSeconds(int|null $seconds = null): self {
        $this->seconds = $seconds;

        return $this;
    }
}
