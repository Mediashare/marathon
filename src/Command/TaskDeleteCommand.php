<?php
namespace Mediashare\Marathon\Command;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\OutputService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TaskDeleteCommand extends Command {
    protected static $defaultName = 'task:delete';
    
    protected function configure() {
        $this
            ->setName('task:delete')
            ->setDescription('<comment>Deleting</comment> the task selected')
            ->addArgument('id', InputArgument::OPTIONAL, 'Task <comment>ID</comment> selected')

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
                $input->getArgument('id') ?? $input->getOption('config-task-id'),
            )->delete()->write();

            // Output render into terminal
            $this->outputService
                ->setOutput($output)
                ->setConfig($this->handlerService->getConfig())
                ->setTask($this->handlerService->getTask())
                ->renderCommits()
                ->renderTasks();

            // Update config
            $this->handlerService->updateCurrentTrackingId();

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
