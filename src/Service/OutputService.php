<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TaskCollection;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;

class OutputService {
    private OutputInterface $output;
    private Table $table;

    private Config $config;
    private TaskCollection|Task $task;
    private int|null $maxWidthOfColumn = null;

    public function setOutput(OutputInterface $output): self {
        $this->output = $output;
        $this->table = new Table($this->output);

        return $this;
    }

    private function getOutput(): OutputInterface {
        return $this->output;
    }

    private function getTable(): Table {
        return $this->table;
    }

    public function setConfig(Config $config): self {
        $this->config = $config;

        return $this;
    }

    private function getConfig(): Config {
        return $this->config;
    }

    public function setTask(TaskCollection|Task $task): self {
        $this->task = $task;

        return $this;
    }

    private function getTask(): TaskCollection|Task {
        return $this->task;
    }

    public function renderTasks(): self {
        $this
            ->getTable()
            ->setColumnMaxWidth(1, $this->getMaxWidthOfColumn())
            ->setHeaders([
                [new TableCell(($this->getTask() instanceof Task) ? 'Task' : 'Tasks', ['colspan' => 7])],
                ['ID', 'Name', 'Status', 'Commits', 'Duration', 'Current step', 'Start date', 'End date']
            ])
            ->setRows(
                ($this->getTask() instanceof Task)
                    ? [$this->getTask()->toRender($this->getConfig())]
                    : $this->getTask()->map(fn (Task $task) => $task->toRender($this->getConfig()))
                    ->toArray()
            )
            ->render()
        ;

        return $this;
    }

    public function renderCommits(): self {
        $this
            ->getTable()
            ->setColumnMaxWidth(2, $this->getMaxWidthOfColumn())
            ->setHeaders([
                [new TableCell('Commits', ['colspan' => 5])],
                ['N°', 'ID', 'Message', 'Duration', 'Total', 'Start date', 'End date']
            ])
            ->setRows(
                $this->getTask()
                    ->getCommits()
                    ->map(
                        fn (Commit $commit)
                            => $commit
                                ->toRender(
                                    $this->getConfig(),
                                    $this->getTask()->getCommits()->getKey($commit) + 1,
                                    array_sum(
                                        $this->getTask()
                                            ->getCommits()
                                            ->allPrevious($commit)
                                            ->map(static fn (Commit $previousCommit) => $previousCommit->getSeconds())
                                            ->toArray(),
                                    ),
                                )
                    )
                    ->toArray(),
            )
            ->render();

        return $this;
    }

    private function getMaxWidthOfColumn(): int {
        if ($this->maxWidthOfColumn):
            return $this->maxWidthOfColumn;
        endif;

        stripos(PHP_OS_FAMILY, 'WIN') === 0
            ? $terminalWidth = (int) shell_exec('powershell -Command "&{(Get-Host).UI.RawUI.WindowSize.Width}"')
            : $terminalWidth = (int) shell_exec('tput cols')
        ;

        return $this->maxWidthOfColumn = $terminalWidth - ($terminalWidth / 1.33);
    }
}