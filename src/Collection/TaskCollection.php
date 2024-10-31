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
        return (new self($this->data))->orderByDayDesc()->first();
    }

    /**
     * @return Task|null
     */
    public function first(): Task|null {
        return $this->data[array_key_first($this->data)] ?? null;
    }

    /**
     * @return self
     */
    public function usort(callable $callback): self {
        $tasks = $this->data;

        usort($tasks, $callback);

        return new self($tasks);
    }

    /**
     * @return self
     */
    public function orderByDayAsc(): self {
        return $this->usort(
            static fn (Task $a, Task $b)
            =>
                ($a->getLastUpdateDate() ?? $a->getStartDate())
                -
                ($b->getLastUpdateDate() ?? $b->getStartDate())
        );
    }

    /**
     * @return self
     */
    public function orderByDayDesc(): self {
        return $this->usort(
            static fn (Task $a, Task $b)
            =>
                ($b->getLastUpdateDate() ?? $b->getStartDate())
                -
                ($a->getLastUpdateDate() ?? $a->getStartDate())
        );
    }

    public function findByName(string $name): self {
        return $this->filter(static fn (Task $task) => $task->getName() === $name);
    }
}