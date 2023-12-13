<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Entity\Timer;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TimerNotFoundException;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TimerService;
use Mediashare\Marathon\Tests\AbstractTestCase;

class TimerServiceTest extends AbstractTestCase {
    private TimerService $timerService;

    protected function setUp(): void
    {
        $config = new Config(
            timerDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'timers',
            timerId: (new \DateTime())->format('YmdHis')
        );
        $this->timerService = (new TimerService(new StepService()))->setConfig($config);
    }

    /**
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     * @throws FileNotFoundException
     */
    public function testCreate(): void {
        $timer = $this->timerService->create(['id' => 'test_id'])->getTimer();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertEquals('test_id', $timer->getId());
    }

    /**
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     * @throws FileNotFoundException
     */
    public function testGetTimer(): void {
        $timer = $this->timerService->getTimer();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertTrue($timer->isRun());
    }

    /**
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     * @throws FileNotFoundException
     */
    public function testStart(): void {
        $timer = $this->timerService->start('Test Timer', '+1 hour')->getTimer();

        $this->assertTrue($timer->isRun());
        $this->assertEquals('Test Timer', $timer->getName());
        $this->assertCount(2, $timer->getSteps());

        $step = $timer->getSteps()->first();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertTrue($step->getEndDate() > $step->getStartDate());

        $step = $timer->getSteps()->last();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNull($step->getEndDate());
    }

    /**
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     * @throws FileNotFoundException
     */
    public function testStop(): void {
        $this->timerService->start('Test Timer', '+1 hour');
        $timer = $this->timerService->stop()->getTimer();

        $this->assertFalse($timer->isRun());
        $this->assertCount(2, $timer->getSteps());

        $step = $timer->getSteps()->first();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertTrue($step->getEndDate() > $step->getStartDate());

        $step = $timer->getSteps()->last();
        $this->assertInstanceOf(Step::class, $step);
        $this->assertSame($step->getEndDate(), $step->getStartDate());
    }

    /**
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     * @throws FileNotFoundException
     */
    public function testArchive(): void {
        $this->timerService->start('Test Timer', '+1 hour');
        $timer = $this->timerService->archive()->getTimer();

        $this->assertTrue($timer->isArchived());
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     */
    public function testDelete(): void {
        $this->timerService->start('Test Timer', '+1 hour');
        $this->timerService->delete();

        $this->expectException(TimerNotFoundException::class);
        $this->timerService->getTimer(createItIfNotExist: false);
    }
}