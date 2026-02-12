<?php

namespace Mediashare\Marathon\Console;

use Mediashare\Marathon\Command\CommitCreateCommand;
use Mediashare\Marathon\Command\CommitDeleteCommand;
use Mediashare\Marathon\Command\CommitEditCommand;
use Mediashare\Marathon\Command\GitGitignoreCommand;
use Mediashare\Marathon\Command\TaskArchiveCommand;
use Mediashare\Marathon\Command\TaskDeleteCommand;
use Mediashare\Marathon\Command\TaskListCommand;
use Mediashare\Marathon\Command\TaskStartCommand;
use Mediashare\Marathon\Command\TaskStatusCommand;
use Mediashare\Marathon\Command\TaskStopCommand;
use Mediashare\Marathon\Command\VersionUpgradeCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class CommandProvider implements CommandLoaderInterface
{
    private const DEFAULT_DEPENDENCIES = ['handlerService', 'outputService'];
    
    /**
     * @var array<string, array{class: class-string<Command>, dependencies?: array<string>}>
     */
    private static array $commandMap = [
        'commit:create' => ['class' => CommitCreateCommand::class],
        'commit:delete' => ['class' => CommitDeleteCommand::class],
        'commit:edit' => ['class' => CommitEditCommand::class],
        'git:gitignore' => ['class' => GitGitignoreCommand::class, 'dependencies' => []],
        'task:archive' => ['class' => TaskArchiveCommand::class],
        'task:delete' => ['class' => TaskDeleteCommand::class],
        'task:list' => ['class' => TaskListCommand::class, 'dependencies' => ['handlerService', 'outputService']],
        'task:start' => ['class' => TaskStartCommand::class],
        'task:status' => ['class' => TaskStatusCommand::class],
        'task:stop' => ['class' => TaskStopCommand::class],
        'version:upgrade' => [
            'class' => VersionUpgradeCommand::class,
            'dependencies' => ['httpClient']
        ],
    ];

    public function __construct(
        private readonly ServiceProviderInterface $serviceLocator
    ) {}

    public function get(string $name): Command
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        return $this->createCommand($name);
    }

    public function has(string $name): bool
    {
        return isset(self::$commandMap[$name]);
    }

    public function getNames(): array
    {
        return array_keys(self::$commandMap);
    }

    private function createCommand(string $name): Command
    {
        $config = self::$commandMap[$name];
        $dependencies = $this->resolveDependencies($config);

        /** @var class-string<Command> $className */
        $className = $config['class'];

        return new $className(...$dependencies);
    }

    /**
     * @param array{class: class-string<Command>, dependencies?: array<string>} $config
     * @return array<object>
     */
    private function resolveDependencies(array $config): array
    {
        $dependencyNames = $config['dependencies'] ?? self::DEFAULT_DEPENDENCIES;

        return array_map(
            fn(string $serviceName): object => $this->serviceLocator->get($serviceName),
            $dependencyNames
        );
    }
}