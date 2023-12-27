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

class CommitCommand extends Command {
    protected static $defaultName = 'commit';

    protected function configure() {
        $this
            ->setName('commit')
            ->setDescription('<comment>Creating</comment> new commit into task')
            ->addArgument('message', InputArgument::OPTIONAL, 'Define a commit <comment>message</comment>', false)
            ->addOption('duration', 'd', InputOption::VALUE_REQUIRED, 'Set the <comment>duration</comment> of the new commit (ex: "<comment>10min</comment>", "<comment>1d</comment>", "<comment>1 day 10 minutes</comment>", "<comment>1h</comment>", "<comment>2 hours</comment>", "<comment>-1hour</comment>")', false)
            ->addOption('task-id', 'tid', InputOption::VALUE_REQUIRED, '<comment>Task ID</comment>')

            // Config
            ->addOption('config-path', 'c', InputOption::VALUE_REQUIRED, 'Set <comment>/file/path/to/json/config</comment>')
            ->addOption('config-datetime-format', 'cdf', InputOption::VALUE_REQUIRED, 'Set DateTimeFormat (ex: "<comment>d/m/Y H:i:s</comment>", "<comment>m/d/Y H:i:s</comment>")')
            ->addOption('config-datetime-zone', 'cdz', InputOption::VALUE_REQUIRED, 'Set DateTimeZone, find different timezones here (<comment>https://www.php.net/manual/en/timezones.php</comment>) <comment>[default: "Europe/Paris"]</comment>')
            ->addOption('config-task-dir', 'ctd', InputOption::VALUE_REQUIRED, 'Set <comment>/directory/path/to/tasks</comment> containing a reports')
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
            // Handler
            $this->handlerService->writeConfig(
                $input->getOption('config-path'),
                $input->getOption('config-datetime-format'),
                $input->getOption('config-datetime-zone'),
                $input->getOption('config-task-dir'),
                $input->getOption('task-id'),
            )->commit(
                $input->getArgument('message'),
                $input->getOption('duration'),
            );

            // Output render into terminal
            $this->outputService
                ->setOutput($output)
                ->setConfig($this->handlerService->getConfig())
                ->setTask($this->handlerService->getTask())
                ->outputRenderCommits()
                ->outputRenderTasks();

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln("");
            $helper = new DescriptorHelper();
            $helper->describe($output, $this);

            if ($this->handlerService->configService->isDebug()):
                $output->writeln("");
                $output->writeln($exception->getTraceAsString());
            endif;

            return Command::FAILURE;
        }
    }
}
