<?php

namespace Mediashare\Marathon\Entity;

use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Trait\EntityUnserializerTrait;

class Config {
    use EntityUnserializerTrait;

    public const CONFIG_PATH = '.'.DIRECTORY_SEPARATOR.'marathon.json';
    public const TASKS_DIRECTORY = '.'.DIRECTORY_SEPARATOR.'.marathon';

    public const DATETIME_FORMAT = 'd/m/Y H:i:s';
    public const DATETIME_ZONE = 'Europe/Paris';

    public const EDITOR = 'nano';

    /**
     * @throws DateTimeZoneException
     */
    public function __construct(
        private string|null $configPath = self::CONFIG_PATH,
        private string|null $dateTimeFormat = self::DATETIME_FORMAT,
        private string|null $dateTimeZone = self::DATETIME_ZONE,
        private string|null $taskDirectory = self::TASKS_DIRECTORY,
        private string|null $editor = self::EDITOR,
        private string|null $taskId = null,
    ) {
        $this->setDateTimeZone($this->dateTimeZone);
    }

    public function setConfigPath(string $configPath): self {
        $this->configPath = $configPath;

        return $this;
    }

    public function getConfigPath(): string {
        return empty($this->configPath) ? self::CONFIG_PATH : $this->configPath;
    }

    public function setTaskDirectory(string $taskDirectory): self {
        $this->taskDirectory = $taskDirectory;

        return $this;
    }

    public function getTaskDirectory(): string {
        return empty($this->taskDirectory) ? self::TASKS_DIRECTORY : $this->taskDirectory;
    }

    public function setDateTimeFormat(string $dateTimeFormat): self {
        $this->dateTimeFormat = $dateTimeFormat;

        return $this;
    }

    public function getDateTimeFormat(): string {
        return empty($this->dateTimeFormat) ? self::DATETIME_FORMAT : $this->dateTimeFormat;
    }

    /**
     * @throws DateTimeZoneException
     */
    public function setDateTimeZone(string $dateTimeZone): self {
        if (empty($dateTimeZone)):
            $dateTimeZone = self::DATETIME_ZONE;
        endif;

        $dateTimeZoneObject = @timezone_open($dateTimeZone);

        if (!$dateTimeZoneObject instanceof \DateTimeZone):
            throw new DateTimeZoneException($dateTimeZone);
        endif;

        $this->dateTimeZone = $dateTimeZoneObject->getName();

        return $this;
    }

    public function getDateTimeZone(): \DateTimeZone {
        return @timezone_open(empty($this->dateTimeZone) ? self::DATETIME_ZONE : $this->dateTimeZone);
    }

    public function setEditor(string $editor): self {
        $this->editor = $editor;

        return $this;
    }

    public function getEditor(): string {
        return empty($this->editor) ? self::EDITOR : $this->editor;
    }

    public function setTaskId(string|null $taskId = null): self {
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
            'editor' => $this->getEditor(),
            'taskId' => $this->getTaskId(),
        ];
    }
}