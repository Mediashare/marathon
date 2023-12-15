<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Collection\CommitCollection;
use Mediashare\Marathon\Collection\StepCollection;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TaskNotFoundException;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\TaskService;
use Mediashare\Marathon\Tests\AbstractTestCase;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;

class CommitServiceTest extends AbstractTestCase {
    private CommitService $commitService;

    /**
     * @throws JsonDecodeException
     * @throws TaskNotFoundException
     * @throws FileNotFoundException
     */
    protected function setUp(): void
    {
        $config = new Config(
            taskDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon' . DIRECTORY_SEPARATOR . 'tasks',
            taskId: (new \DateTime())->format('YmdHis')
        );

        $this->commitService = (new CommitService(
            $stepService = new StepService()
        ))->setTask((new TaskService($stepService))->setConfig($config)->getTask());
    }

    public function testCreate(): void {
        $task = $this->commitService->create('Test Commit', '+2 hours')->getTask();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertCount(1, $commits = $task->getCommits());

        $this->assertInstanceOf(CommitCollection::class, $commits);
        $this->assertInstanceOf(Commit::class, $commit = $commits->first());

        $this->assertInstanceOf(StepCollection::class, $steps = $commit->getSteps());
        $this->assertInstanceOf(Step::class, $lastStep = $steps->first());
        $this->assertEquals("02:00:00", $lastStep->getDuration());

        $this->assertCount(1, $steps = $task->getSteps());
        $this->assertInstanceOf(StepCollection::class, $steps);
        $this->assertInstanceOf(Step::class, $steps->first());
    }

    /**
     * @throws CommitNotFoundException
     */
    public function testEdit(): void {
        $task = $this->commitService->create('Original Commit', '+1 hour')->getTask();
        $originalCommitId = $task->getCommits()->first()->getId();

        $task = $this->commitService->edit($originalCommitId, 'Updated Commit', '+30 minutes')->getTask();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertCount(1, $task->getCommits());
        $this->assertCount(1, $task->getSteps());

        $editedCommit = $task->getCommits()->first();
        $this->assertEquals('Updated Commit', $editedCommit->getMessage());
    }

    /**
     * @throws CommitNotFoundException
     */
    public function testDelete(): void {
        $task = $this->commitService->create('To Be Removed Commit', '+3 hours')->getTask();
        $toBeRemovedCommitId = $task->getCommits()->first()->getId();

        $task = $this->commitService->delete($toBeRemovedCommitId)->getTask();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertCount(0, $task->getCommits());
        $this->assertCount(1, $task->getSteps());
    }

    public function testRemoveNonexistent(): void {
        $this->expectException(CommitNotFoundException::class);

        $this->commitService->delete('NonexistentCommitId');
    }
}