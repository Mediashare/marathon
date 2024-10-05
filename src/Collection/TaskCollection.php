<?php

namespace Mediashare\Marathon\Collection;

use Ramsey\Collection\AbstractCollection;
use Mediashare\Marathon\Entity\Task;

class TaskCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Task::class;
    }

    /**
     * @return Task|null
     */
    public function last(): Task|null {
        return $this->data[array_key_last($this->data)] ?? null;
    }

    /**
     * @return Task|null
     */
    public function lastUpdated(): Task|null {
        return (new TaskCollection($this->data))->orderByDayDesc()->first();
    }

    /**
     * @return Task|null
     */
    public function first(): Task|null {
        return $this->data[array_key_first($this->data)] ?? null;
    }

    /**
     * @return Task[]
     */
    public function usort(callable $callback): TaskCollection {
        $tasks = $this->data;

        usort($tasks, $callback);

        return new TaskCollection($tasks);
    }

    /**
     * @return Task[]
     */
    public function orderByDayAsc(): TaskCollection {
        return $this->usort(
            static fn (Task $a, Task $b)
            =>
                ($a->getLastUpdateDate() ?? $a->getEndDate() ?? $a->getStartDate())
                -
                ($b->getLastUpdateDate() ?? $b->getEndDate() ?? $b->getStartDate())
        );
    }

    /**
     * @return Task[]
     */
    public function orderByDayDesc(): TaskCollection {
        return $this->usort(
            static fn (Task $a, Task $b)
            =>
                ($b->getLastUpdateDate() ?? $b->getEndDate() ?? $b->getStartDate())
                -
                ($a->getLastUpdateDate() ?? $a->getEndDate() ?? $a->getStartDate())
        );
    }
}