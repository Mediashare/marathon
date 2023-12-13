<?php

namespace Mediashare\Marathon\Entity;

class Config {
    public const CONFIG_PATH = '.'.DIRECTORY_SEPARATOR.'.marathon'.DIRECTORY_SEPARATOR.'config.json';
    public const TASKS_DIRECTORY = '.'.DIRECTORY_SEPARATOR.'.marathon'.DIRECTORY_SEPARATOR.'tasks';

    public const DATETIME_FORMAT = 'd/m/Y H:i:s';

    public function __construct(
        private string|null $dateTimeFormat = self::DATETIME_FORMAT,
        private string|null $taskDirectory = null,
        private string|null $taskId = null,
    ) { }

    public function setTaskDirectory(string $taskDirectory): self {
        $this->taskDirectory = $taskDirectory;

        return $this;
    }

    public function getTaskDirectory(): string|null {
        return $this->taskDirectory ?? self::TASKS_DIRECTORY;
    }

    public function setDateTimeFormat(string $dateTimeFormat): self {
        $this->dateTimeFormat = $dateTimeFormat;

        return $this;
    }

    public function getDateTimeFormat(): string {
        return $this->dateTimeFormat;
    }

    public function setTaskId(string $taskId): self {
        $this->taskId = $taskId;

        return $this;
    }

    public function getTaskId(): string|null {
        return $this->taskId;
    }

    public function toArray(): array {
        return [
            'taskDirectory' => $this->getTaskDirectory(),
            'dateTimeFormat' => $this->getDateTimeFormat(),
            'taskId' => $this->getTaskId(),
        ];
    }
}
