<?php
namespace Mediashare\Marathon\Command;

use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\OutputService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskStatusCommand extends Command {
    protected static $defaultName = 'task:status';
    
    protected function configure() {
        $this
            ->setName('task:status')
            ->setDescription('<comment>Displaying</comment> status of task')
            ->addArgument('task-id', InputArgument::OPTIONAL, '<comment>Task ID</comment>')

            // Config
            ->addOption('config-path', 'c', InputOption::VALUE_REQUIRED, 'Set <comment>/file/path/to/json/config</comment>')
            ->addOption('config-datetime-format', 'cdf', InputOption::VALUE_REQUIRED, 'Set DateTimeFormat (ex: "<comment>d/m/Y H:i:s</comment>", "<comment>m/d/Y H:i:s</comment>")')
            ->addOption('config-datetime-zone', 'cdz', InputOption::VALUE_REQUIRED, 'Set DateTimeZone, find different timezones here [<comment>https://www.php.net/manual/en/timezones.php</comment>] (default: "<comment>Europe/Paris</comment>")')
            ->addOption('config-task-dir', 'ctd', InputOption::VALUE_REQUIRED, 'Set <comment>/directory/path/to/tasks</comment> containing a reports')
        ;
    }

    public function __construct(
        private HandlerService $handlerService,
        private OutputService $outputService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        try {
            // Handler
            $this->handlerService->writeConfig(
                $input->getOption('config-path'),
                $input->getOption('config-datetime-format'),
                $input->getOption('config-datetime-zone'),
                $input->getOption('config-task-dir'),
                $input->getArgument('task-id'),
            );

            // Output render into terminal
            $this->outputService
                ->setOutput($output)
                ->setConfig($this->handlerService->getConfig())
                ->setTask($this->handlerService->getTask())
                ->renderCommits()
                ->renderTasks();

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
