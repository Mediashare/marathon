<?php
namespace Mediashare\Marathon\Command;

use Mediashare\Marathon\Service\HandlerService;
use Mediashare\Marathon\Service\OutputService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommitEditCommand extends Command {
    protected static $defaultName = 'commit:edit';

    protected function configure() {
        $this
            ->setName('commit:edit')
            ->setAliases([
                'edit',
            ])
            ->setDescription('<comment>Editing</comment> the commit from task')
            ->addArgument('commit-id', InputArgument::REQUIRED, '<comment>Commit ID</comment>')
            ->addOption('editor', 'e', InputOption::VALUE_NONE, 'Open default message <comment>editor</comment>')
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Update a commit <comment>message</comment>', false)
            ->addOption('duration', 'd', InputOption::VALUE_REQUIRED, 'Update the <comment>duration</comment> of the commit (ex: "<comment>10min</comment>", "<comment>1d</comment>", "<comment>1 day 10 minutes</comment>", "<comment>1h</comment>", "<comment>2 hours</comment>", "<comment>-1hour</comment>")', false)
            ->addOption('task-id', 't', InputOption::VALUE_REQUIRED, '<comment>Task ID</comment>', null)

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
            $this->handlerService->writeConfig(
                $input->getOption('config-path'),
                $input->getOption('config-task-dir'),
                $input->getOption('config-editor'),
                $input->getOption('task-id'),
            )->commitEdit(
                $input->getArgument('commit-id'),
                $input->getOption('message'),
                $input->getOption('duration'),
                $input->getOption('editor'),
            );

            // Output render into terminal
            $this->outputService
                ->setConfig($this->handlerService->getConfig())
                ->setTask($this->handlerService->getTask())
                ->setInput($input)
                ->outputRenderTask();

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $helper = new DescriptorHelper();
            $helper->describe($output, $this);

            if ($this->handlerService->configService->isDebug()):
                $output->writeln("");
                $output->writeln($exception->getTraceAsString());
            endif;

            $output->writeln("");
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
