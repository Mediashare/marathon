<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Exception\DateTimeZoneException;
use Mediashare\Marathon\Exception\StrToTimeDurationException;
use Mediashare\Marathon\Service\CommitService;
use Mediashare\Marathon\Service\StepService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Service\OutputService;

class OutputServiceTest extends KernelTestCase {
    private OutputService $outputService;
    private Config $config;
    private BufferedOutput $output;
    private CommitService $commitService;
    private StepService $stepService;

    /**
     * @throws DateTimeZoneException
     */
    public function setUp(): void {
        parent::setUp();

        $marathonDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'marathon';

        $this->config = new Config(
            configPath: $marathonDirectory . DIRECTORY_SEPARATOR . 'config.json',
            dateTimeFormat: 'd/m/Y H:i:s',
            taskDirectory: $marathonDirectory . DIRECTORY_SEPARATOR . 'tasks',
        );

        $this->output = new BufferedOutput();

        $this->outputService = new OutputService();

        $this->commitService = new CommitService($this->stepService = new StepService());
    }

    /**
     * @throws StrToTimeDurationException
     */
    public function testRenderTask(): void
    {
        $task = (new Task())
            ->setId('task_id')
            ->setName('task_name')
            ->setRun(true)
            ->setArchived(false)
            ->addCommit($this->commitService
                ->setTask(new Task())
                ->create(duration: '1h')
                ->getTask()
                ->getCommits()->first()
            )->addStep($firstStep = $this->stepService->createWithCustomDuration('2hours 10min'))
            ->addStep($lastStep = $this->stepService->createWithCustomDuration('3hours 20min'))
        ;

        $this->outputService->setOutput($this->output)->setConfig($this->config);

        $renderedTask = $this->outputService->renderTask($task);

        $expectedOutput = [
            'id' => 'task_id',
            'name' => 'task_name',
            'running' => 'Running',
            'commits' => 1,
            'duration' => '06:30:00',
            'current_steps' => '05:30:00',
            'startDate' => $task->getStartDateFormated($this->config),
            'endDate' => $task->getEndDateFormated($this->config),
        ];

        $this->assertEquals($expectedOutput, $renderedTask);
    }

    /**
     * @throws StrToTimeDurationException
     */
    public function testRenderCommit(): void
    {
        $commit = (new Commit())
            ->setId('commit_id')
            ->setMessage('commit_message')
            ->addStep($this->stepService->createWithCustomDuration('1hours 30min'))
            ->addStep($this->stepService->createWithCustomDuration('1hour'))
        ;

        $this->outputService->setOutput($this->output)->setConfig($this->config);

        $renderedTask = $this->outputService->renderCommit($commit, $index = 1, 120);

        $expectedOutput = [
            'index' => $index,
            'id' => 'commit_id',
            'message' => 'commit_message',
            'duration' => '02:30:00',
            'duration_total' => '02:32:00',
            'startDate' => $commit->getStartDateFormated($this->config),
            'endDate' => $commit->getEndDateFormated($this->config),
        ];

        $this->assertEquals($expectedOutput, $renderedTask);
    }

    public function testGetMaxWidthOfColumn(): void
    {
        $actualWidthOfColumn = stripos(PHP_OS_FAMILY, 'WIN') === 0
            ? (int) shell_exec('powershell -Command "&{(Get-Host).UI.RawUI.WindowSize.Width}"')
            : (int) shell_exec('tput cols')
        ;

        $expected = $actualWidthOfColumn - ($actualWidthOfColumn / 1.33);

        $this->outputService->setMaxWidthOfColumn();

        $this->assertEquals((int) $expected, $this->outputService->getMaxWidthOfColumn());
    }
}
