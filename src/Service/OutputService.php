<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TimerCollection;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Timer;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;

class OutputService {
    private OutputInterface $output;
    private Table $table;

    private Config $config;
    private TimerCollection|Timer $timer;

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

    public function setTimer(TimerCollection|Timer $timer): self {
        $this->timer = $timer;

        return $this;
    }

    private function getTimer(): TimerCollection|Timer {
        return $this->timer;
    }

    public function renderTimers(): self {
        $this->getTable()->setHeaders([
                [new TableCell(($this->getTimer() instanceof Timer) ? 'Timer' : 'Timers', ['colspan' => 7])],
                ['ID', 'Name', 'Status', 'Commits', 'Duration', 'Current step', 'Start date', 'End date']
            ])
            ->setRows(
                ($this->getTimer() instanceof Timer)
                    ? [$this->getTimer()->toRender($this->getConfig()->getDateTimeFormat())]
                    : $this->getTimer()->map(static fn (Timer $timer) => $timer->toRender($this->getConfig()->getDateTimeFormat()))
                    ->toArray()
            )
            ->render()
        ;

        return $this;
    }

    public function renderCommits(): self {
        $this->getTable()->setHeaders([
                [new TableCell('Commits', ['colspan' => 5])],
                ['N°', 'ID', 'Message', 'Duration', 'Total', 'Start date', 'End date']
            ])
            ->setRows(
                $this->getTimer()
                    ->getCommits()
                    ->map(
                        static fn (Commit $commit)
                            => $commit
                                ->toRender(
                                    $this->getTimer()->getCommits()->getKey($commit) + 1,
                                    array_sum(
                                        $this->getTimer()
                                            ->getCommits()
                                            ->allPrevious($commit)
                                            ->map(static fn (Commit $previousCommit) => $previousCommit->getSeconds())
                                            ->toArray(),
                                    ),
                                    $this->getConfig()->getDateTimeFormat()
                                )
                    )
                    ->toArray(),
            )
            ->render();

        return $this;
    }
}