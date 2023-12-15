<?php
namespace Mediashare\Marathon\Command;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\OutputService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskStartCommand extends Command {
    protected static $defaultName = 'task:start';
    
    protected function configure() {
        $this
            ->setName('task:start')
            ->setDescription('<comment>Starting</comment> step of task selected')
            ->addArgument('name', InputArgument::OPTIONAL, 'Set the <comment>name</comment> of task selected', false)
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Starting task by <comment>ID</comment> selected')
            ->addOption('new', null, InputOption::VALUE_NONE, 'Starting <comment>new</comment> task')
            ->addOption('duration', 'd', InputOption::VALUE_REQUIRED, 'Set the <comment>duration</comment> of the current step (ex: "<comment>+1minutes</comment>", "<comment>+10min</comment>", "<comment>+1hours</comment>", "<comment>+1days</comment>", "<comment>-1hour</comment>")', false)

            // Config
            ->addOption('config-path', 'c', InputOption::VALUE_REQUIRED, 'Config <comment>path</comment> to json file')
            ->addOption('config-datetime-format', 'cdf', InputOption::VALUE_REQUIRED, 'Set DateTime format (ex: <comment>"d/m/Y H:i:s"</comment>, <comment>"m/d/Y H:i:s"</comment>)', Config::DATETIME_FORMAT)
            ->addOption('config-task-dir', 'ctd', InputOption::VALUE_REQUIRED, 'Set directory path containing a tasks files')
            ->addOption('config-task-id', 'cti', InputOption::VALUE_REQUIRED, 'Task <comment>ID</comment> selected in config')
        ;
    }

    public function __construct(
        private HandlerService $handlerService,
        private OutputService $outputService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            // Handler
            $this->handlerService->setConfig(
                $input->getOption('config-path'),
                $input->getOption('config-datetime-format'),
                $input->getOption('config-task-dir'),
                $input->getOption('new')
                    ? $input->getOption('id') ?? $input->getOption('config-task-id') ?? (new \DateTime())->format('YmdHis')
                    : $input->getOption('id') ?? $input->getOption('config-task-id'),
            )->start(
                $input->getArgument('name'),
                $input->getOption('duration'),
            )->write();

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
