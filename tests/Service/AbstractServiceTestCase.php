<?php

namespace Mediashare\Marathon\Tests\Service;

use PHPUnit\Framework\TestCase;

class AbstractServiceTestCase extends TestCase {
    public string $marathonDirectory;
    public string $configPath;
    public string $taskDirectory;

    public function setUp(): void {
        $this->marathonDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon';
        $this->configPath = $this->marathonDirectory . DIRECTORY_SEPARATOR . 'config.json';
        $this->taskDirectory = $this->marathonDirectory . DIRECTORY_SEPARATOR . 'tasks';

        @mkdir($this->marathonDirectory, recursive: true);
        @mkdir($this->taskDirectory, recursive: true);
    }

    public function tearDown(): void {
        $this->rmdir($this->marathonDirectory);
    }

    private function rmdir(string $directory): void {
        if (is_dir($directory)) {
            $directories = scandir($directory);
            foreach ($directories as $object) {
                if ($object !== "." && $object !== "..") {
                    if (is_dir($directory. DIRECTORY_SEPARATOR .$object) && !is_link($directory."/".$object))
                        $this->rmdir($directory. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($directory. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($directory);
        }
    }
}