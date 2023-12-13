<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Timer;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TimerNotFoundException;
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

    }

    public function testUpdateCurrentTrackingId(): void {

    }

    public function testGetTimer(): void {

    }

    public function testGetTimers(): void {

    }

    public function testStart(): void {

    }

    public function testStop(): void {

    }

    public function testArchive(): void {

    }

    public function testDelete(): void {

    }

    public function testCommit(): void {

    }

    public function testWrite(): void {

    }

}