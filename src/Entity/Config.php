<?php

namespace Mediashare\Marathon\Entity;

use Mediashare\Marathon\Trait\EntityUnserializerTrait;

class Config {
    use EntityUnserializerTrait;

    public const CONFIG_PATH = '.'.DIRECTORY_SEPARATOR.'marathon.json';
    public const TASKS_DIRECTORY = '.'.DIRECTORY_SEPARATOR.'.marathon';

    public const EDITOR = 'nano';

    public function __construct(
        private string|null $configPath = self::CONFIG_PATH,
        private string|null $taskDirectory = self::TASKS_DIRECTORY,
        private string|null $editor = self::EDITOR,
        private string|null $taskId = null,
    ) { }

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
            'editor' => $this->getEditor(),
            'taskId' => $this->getTaskId(),
        ];
    }
}