<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Collection\CommitCollection;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\CommandMissingLeastOnceOptionException;
use Mediashare\Marathon\Exception\StrToTimeDurationException;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Service\StepService;
use Mediashare\Marathon\Service\EditorService;

class CommitServiceTest extends AbstractServiceTestCase {
    private CommitService $commitService;

    public function setUp(): void {
        parent::setUp();

        $this->commitService = new CommitService($this->createMock(StepService::class), $this->createMock(EditorService::class));
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
     * @throws CommandMissingLeastOnceOptionException
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
     * @throws CommandMissingLeastOnceOptionException
     */
    public function testEditCommitWithDuration(): void {
        $task = new Task();
        $commit = (new Commit())->setId($commitId = 'your_commit_id')->setMessage($message = 'test_commit_message');
        $task->getCommits()->add($commit);

        $this->commitService->setTask($task);

        $task = $this->commitService->edit($commitId, false, '2 hours')->getTask();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($message, $task->getCommits()->first()->getMessage());
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

    public function testCommitNotFoundExceptionOnDelete(): void {
        $this->expectException(CommitNotFoundException::class);

        $task = $this->createMock(Task::class);
        $commitId = 'nonexistent_commit_id';

        $this->commitService->setTask($task);

        $this->commitService->delete($commitId);
    }

    /**
     * @throws StrToTimeDurationException
     * @throws CommandMissingLeastOnceOptionException
     */
    public function testCommitNotFoundExceptionOnEdit(): void {
        $this->expectException(CommitNotFoundException::class);

        $task = $this->createMock(Task::class);
        $commitId = 'nonexistent_commit_id';

        $this->commitService->setTask($task);

        $this->commitService->edit($commitId);
    }

    /**
     * @throws CommitNotFoundException
     * @throws StrToTimeDurationException
     */
    public function testCommandMissingLeastOnceOptionExceptionOnEdit(): void {
        $commit = $this->createMock(Commit::class);

        $commitCollection = $this->createMock(CommitCollection::class);
        $commitCollection->expects($this->once())->method('findOneBy')->willReturn($commit);

        $task = $this->createMock(Task::class);
        $task->expects($this->once())->method('getCommits')->willReturn($commitCollection);

        $this->commitService->setTask($task);

        $this->expectException(CommandMissingLeastOnceOptionException::class);
        $this->commitService->edit('commit_edit_test_without_parameters');
    }
}