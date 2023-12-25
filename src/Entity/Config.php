<?php

namespace Mediashare\Marathon\Entity;

use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Trait\EntityUnserializerTrait;

class Config {
    use EntityUnserializerTrait;

    public const CONFIG_PATH = '.'.DIRECTORY_SEPARATOR.'.marathon'.DIRECTORY_SEPARATOR.'config.json';
    public const TASKS_DIRECTORY = '.'.DIRECTORY_SEPARATOR.'.marathon'.DIRECTORY_SEPARATOR.'tasks';

    public const DATETIME_FORMAT = 'd/m/Y H:i:s';
    public const DATETIME_ZONE = 'Europe/Paris';

    /**
     * @throws DateTimeZoneException
     */
    public function __construct(
        private string|null $configPath = self::CONFIG_PATH,
        private string|null $dateTimeFormat = self::DATETIME_FORMAT,
        private string|null $dateTimeZone = self::DATETIME_ZONE,
        private string|null $taskDirectory = self::TASKS_DIRECTORY,
        private string|null $taskId = null,
    ) {
        $this->setDateTimeZone($this->dateTimeZone);
    }

    public function setConfigPath(string $configPath): self {
        $this->configPath = $configPath;

        return $this;
    }

    public function getConfigPath(): string {
        return $this->configPath;
    }

    public function setTaskDirectory(string $taskDirectory): self {
        $this->taskDirectory = $taskDirectory;

        return $this;
    }

    public function getTaskDirectory(): string {
        return $this->taskDirectory;
    }

    public function setDateTimeFormat(string $dateTimeFormat): self {
        $this->dateTimeFormat = $dateTimeFormat;

        return $this;
    }

    public function getDateTimeFormat(): string {
        return $this->dateTimeFormat;
    }

    /**
     * @throws DateTimeZoneException
     */
    public function setDateTimeZone(string $dateTimeZone): self {
        $dateTimeZone = @timezone_open($dateTimeZone);

        if (!$dateTimeZone instanceof \DateTimeZone):
            throw new DateTimeZoneException();
        endif;

        $this->dateTimeZone = $dateTimeZone->getName();

        return $this;
    }

    public function getDateTimeZone(): \DateTimeZone {
        return timezone_open($this->dateTimeZone);
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
            'configPath' => $this->getConfigPath(),
            'taskDirectory' => $this->getTaskDirectory(),
            'dateTimeFormat' => $this->getDateTimeFormat(),
            'dateTimeZone' => $this->getDateTimeZone()->getName(),
            'taskId' => $this->getTaskId(),
        ];
    }
}
