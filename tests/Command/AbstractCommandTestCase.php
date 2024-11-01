<?php

namespace Mediashare\Marathon\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractCommandTestCase extends KernelTestCase {
    public string $marathonDirectory;
    public string $configPath;
    public string $taskDirectory;

    public Application $application;

    public function setUp(): void {
        parent::setUp();
        
        $this->marathonDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon';
        $this->configPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon.json';
        $this->taskDirectory = $this->marathonDirectory;

        @mkdir($this->marathonDirectory, recursive: true);
        @mkdir($this->taskDirectory, recursive: true);

        self::bootKernel();
        $this->application = new Application(self::$kernel);
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