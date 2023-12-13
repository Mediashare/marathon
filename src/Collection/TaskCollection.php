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

    public function last(): Task|null {
        return $this->data[array_key_last($this->data)] ?? null;
    }

    public function first(): Task|null {
        return $this->data[array_key_first($this->data)] ?? null;
    }
}