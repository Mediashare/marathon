<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Collection\CommitCollection;
use Mediashare\Marathon\Collection\StepCollection;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TimerNotFoundException;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TimerService;
use Mediashare\Marathon\Tests\AbstractTestCase;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Timer;
use Mediashare\Marathon\Exception\CommitNotFoundException;

class CommitServiceTest extends AbstractTestCase {
    private CommitService $commitService;

    /**
     * @throws JsonDecodeException
     * @throws TimerNotFoundException
     * @throws FileNotFoundException
     */
    protected function setUp(): void
    {
        $config = new Config(
            timerDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'timers',
            timerId: (new \DateTime())->format('YmdHis')
        );

        $this->commitService = (new CommitService(
            $stepService = new StepService()
        ))->setTimer((new TimerService($stepService))->setConfig($config)->getTimer());
    }

    public function testCreate(): void {
        $timer = $this->commitService->create('Test Commit', '+2 hours')->getTimer();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertCount(1, $commits = $timer->getCommits());

        $this->assertInstanceOf(CommitCollection::class, $commits);
        $this->assertInstanceOf(Commit::class, $commit = $commits->first());

        $this->assertInstanceOf(StepCollection::class, $steps = $commit->getSteps());
        $this->assertInstanceOf(Step::class, $lastStep = $steps->first());
        $this->assertEquals("02:00:00", $lastStep->getDuration());

        $this->assertCount(1, $steps = $timer->getSteps());
        $this->assertInstanceOf(StepCollection::class, $steps);
        $this->assertInstanceOf(Step::class, $steps->first());
    }

    /**
     * @throws CommitNotFoundException
     */
    public function testEdit(): void {
        $timer = $this->commitService->create('Original Commit', '+1 hour')->getTimer();
        $originalCommitId = $timer->getCommits()->first()->getId();

        $timer = $this->commitService->edit($originalCommitId, 'Updated Commit', '+30 minutes')->getTimer();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertCount(1, $timer->getCommits());
        $this->assertCount(1, $timer->getSteps());

        $editedCommit = $timer->getCommits()->first();
        $this->assertEquals('Updated Commit', $editedCommit->getMessage());
    }

    /**
     * @throws CommitNotFoundException
     */
    public function testDelete(): void {
        $timer = $this->commitService->create('To Be Removed Commit', '+3 hours')->getTimer();
        $toBeRemovedCommitId = $timer->getCommits()->first()->getId();

        $timer = $this->commitService->delete($toBeRemovedCommitId)->getTimer();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertCount(0, $timer->getCommits());
        $this->assertCount(1, $timer->getSteps());
    }

    public function testRemoveNonexistent(): void {
        $this->expectException(CommitNotFoundException::class);

        $this->commitService->delete('NonexistentCommitId');
    }
}