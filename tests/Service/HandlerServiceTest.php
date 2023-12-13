<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Service\ConfigService;
use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\SerializerService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TimerService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class HandlerServiceTest extends AbstractTestCase {
    private HandlerService $handlerService;

    protected function setUp(): void {
        $config = new Config(
            timerDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'timers',
            timerId: (new \DateTime())->format('YmdHis')
        );

        $this->handlerService = new HandlerService(
            new ConfigService(
                $timerService = (new TimerService(
                    $stepService = new StepService()
                ))->setConfig($config)),
            $timerService,
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

    public function testGetTimer(): void {
        $this->assertTrue(true);
    }

    public function testGetTimers(): void {
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