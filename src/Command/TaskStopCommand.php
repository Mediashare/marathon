<?php
namespace Mediashare\Marathon\Command;

use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\OutputService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'task:stop',
    description: '<comment>Stoping</comment> task step',
    aliases: ['stop', 'pause'],
)]
class TaskStopCommand extends Command {
    protected static $defaultName = 'task:stop';
    
    protected function configure() {
        $this
            ->addArgument('task-id', InputArgument::OPTIONAL, 'Task <comment>ID</comment> or <comment>name</comment>', null)
            ->addOption('duration', 'd', InputOption::VALUE_REQUIRED, 'Set the <comment>duration</comment> of the current step (ex: "<comment>10min</comment>", "<comment>1d</comment>", "<comment>1 day 10 minutes</comment>", "<comment>1h</comment>", "<comment>2 hours</comment>", "<comment>-1hour</comment>")', false)
            ->addOption('remaining', 'r', InputOption::VALUE_REQUIRED, 'Set the <comment>remaining</comment> expected for task (ex: "<comment>10min</comment>", "<comment>1d</comment>", "<comment>1 day 10 minutes</comment>", "<comment>1h</comment>", "<comment>2 hours</comment>")', false)
            ->addOption('name', 'N', InputOption::VALUE_REQUIRED, 'Set the task <comment>name</comment>', false)
            ->addOption('new', null, InputOption::VALUE_NONE, 'Creating <comment>new task</comment>')

            // Config
            ->addOption('config-path', 'c', InputOption::VALUE_REQUIRED, 'Set <comment>/file/path/to/json/config</comment>', false)
            ->addOption('config-task-dir', 'p', InputOption::VALUE_REQUIRED, 'Set <comment>/directory/path/to/tasks</comment> containing a reports', false)
            ->addOption('config-editor', 'E', InputOption::VALUE_REQUIRED, 'Set default <comment>editor</comment> (ex: "<comment>nano</comment>", "<comment>vim</comment>")', false)
        ;
    }

    public function __construct(
        private readonly HandlerService $handlerService,
        private readonly OutputService $outputService,
    ) {
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int {
        try {
            // Preload max width output
            $this->outputService->setMaxWidthOfColumn();

            // Handler
            $this->handlerService->init(
                $input->getOption('config-path'),
                $input->getOption('config-task-dir'),
                $input->getOption('config-editor'),
                $input->getOption('new')
                    ? ($input->getArgument('task-id') === null)
                        ? (new \DateTime())->format('YmdHis')
                        : $input->getArgument('task-id')
                    : $input->getArgument('task-id'),
            )->taskStop(
                name: $input->getOption('name'),
                duration: $input->getOption('duration'),
                remaining: $input->getOption('remaining'),
            );

            // Output render into terminal
            $this->outputService
                ->setConfig($this->handlerService->getConfigService()->getConfig())
                ->setTask($this->handlerService->getTask())
                ->setInput($input)
                ->outputRenderTask();

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $helper = new DescriptorHelper();
            $helper->describe($output, $this);

            if ($this->handlerService->getConfigService()->isDebug()):
                $output->writeln("");
                $output->writeln($exception->getTraceAsString());
            endif;

            $output->writeln("");
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
