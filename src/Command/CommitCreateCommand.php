<?php
namespace Mediashare\Marathon\Command;

use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\OutputService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'commit:create',
    description: '<comment>Creating</comment> new commit into task',
    aliases: ['commit', 'beer'],
)]
class CommitCreateCommand extends Command {
    protected static $defaultName = 'commit:create';

    protected function configure() {
        $this
            ->addArgument('message', InputArgument::OPTIONAL, 'Define a commit <comment>message</comment>', false)
            ->addOption('editor', 'e', InputOption::VALUE_NONE, 'Open default message <comment>editor</comment>')
            ->addOption('duration', 'd', InputOption::VALUE_REQUIRED, 'Set the <comment>duration</comment> of the new commit (ex: "<comment>10min</comment>", "<comment>1d</comment>", "<comment>1 day 10 minutes</comment>", "<comment>1h</comment>", "<comment>2 hours</comment>", "<comment>-1hour</comment>")', false)
            ->addOption('task-id', 't', InputOption::VALUE_REQUIRED, 'Task <comment>ID</comment> or <comment>name</comment>', null)

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
                $input->getOption('task-id'),
            )->commit(
                $input->getArgument('message'),
                $input->getOption('duration'),
                $input->getOption('editor'),
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
