<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\SerializerService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TaskService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class HandlerServiceTest extends AbstractTestCase {
    private HandlerService $handlerService;

    protected function setUp(): void {
        $config = new Config(
            taskDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'tasks',
            taskId: (new \DateTime())->format('YmdHis')
        );

        $this->handlerService = new HandlerService(
            new ConfigService(
                $taskService = (new TaskService(
                    $stepService = new StepService()
                ))->setConfig($config)),
            $taskService,
            new CommitService($stepService),
            new SerializerService(),
        );
    }

    public function testSetConfig(): void {
        $this->assertTrue(true);
    }

    public function testUpdateCurrentTrackingId(): void {
        $this->assertTrue(true);
    }

    public function testGetTask(): void {
        $this->assertTrue(true);
    }

    public function testGetTasks(): void {
        $this->assertTrue(true);
    }

    public function testStart(): void {
        $this->assertTrue(true);
    }

    public function testStop(): void {
        $this->assertTrue(true);
    }

    public function testArchive(): void {
        $this->assertTrue(true);
    }

    public function testDelete(): void {
        $this->assertTrue(true);
    }

    public function testCommit(): void {
        $this->assertTrue(true);
    }

    public function testWrite(): void {
        $this->assertTrue(true);
    }

}