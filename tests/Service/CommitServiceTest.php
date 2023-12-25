<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\StrToTimeDurationException;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Service\StepService;

class CommitServiceTest extends AbstractServiceTestCase {
    private CommitService $commitService;

    public function setUp(): void {
        parent::setUp();

        $this->commitService = new CommitService($this->createMock(StepService::class));
    }

    /**
     * @throws StrToTimeDurationException
     */
    public function testCreateCommit(): void {
        $task = new Task();
        $this->commitService->setTask($task);

        $this->commitService->create();

        $this->assertCount(1, $task->getCommits());
        $this->assertCount(1, $task->getCommits()->first()->getSteps());
        $this->assertNull($task->getCommits()->first()->getMessage());
    }

    /**
     * @throws StrToTimeDurationException
     */
    public function testCreateCommitWithMessage(): void {
        $task = new Task();
        $this->commitService->setTask($task);

        $commitMessage = 'Test Commit';
        $duration = '1 hour';

        $this->commitService->create($commitMessage, $duration);

        $this->assertCount(1, $task->getCommits());
        $this->assertCount(1, $task->getCommits()->first()->getSteps());
        $this->assertEquals($commitMessage, $task->getCommits()->first()->getMessage());
    }

    /**
     * @throws StrToTimeDurationException
     */
    public function testCreateCommitWithDuration(): void {
        $task = new Task();
        $this->commitService->setTask($task);

        $duration = '1 hour';

        $this->commitService->create(duration: $duration);

        $this->assertCount(1, $task->getCommits());
        $this->assertCount(1, $task->getCommits()->first()->getSteps());
        $this->assertNull($task->getCommits()->first()->getMessage());
    }

    /**
     * @throws CommitNotFoundException
     * @throws StrToTimeDurationException
     */
    public function testEditCommitMessage(): void {
        $task = new Task();
        $commitId = '123456789';

        $commit = new Commit();
        $commit->setId($commitId);
        $task->addCommit($commit);

        $this->commitService->setTask($task);

        $newMessage = 'Updated Message';
        $this->commitService->edit($commitId, $newMessage);

        $this->assertCount(1, $task->getCommits());
        $this->assertEquals($newMessage, $task->getCommits()->first()->getMessage());
    }

    /**
     * @throws CommitNotFoundException
     * @throws StrToTimeDurationException
     */
    public function testEditCommitDuration(): void {
        $task = new Task();
        $commitId = '123456789';

        $commit = new Commit();
        $commit->setId($commitId);
        $task->addCommit($commit);

        $this->commitService->setTask($task);

        $newDuration = '2 hours';
        $this->commitService->edit($commitId, false, $newDuration);

        $this->assertCount(1, $task->getCommits());
        $this->assertCount(1, $task->getCommits()->first()->getSteps());
        $this->assertCount(1, $task->getCommits()->first()->getSteps());
    }

    /**
     * @throws CommitNotFoundException
     */
    public function testDeleteCommit(): void {
        $task = new Task();
        $commitId = '123456789';

        $commit = new Commit();
        $commit->setId($commitId);
        $task->addCommit($commit);

        $this->commitService->setTask($task);

        $this->commitService->delete($commitId);

        $this->assertCount(0, $task->getCommits());
    }

    public function testCommitNotFoundException(): void {
        $this->expectException(CommitNotFoundException::class);

        $task = new Task();
        $commitId = 'nonexistent_commit_id';

        $this->commitService->setTask($task);

        $this->commitService->delete($commitId);
    }
}