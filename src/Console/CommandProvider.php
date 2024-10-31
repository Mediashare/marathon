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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class CommandProvider implements CommandLoaderInterface
{
    private array $commandMap;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ServiceProviderInterface $serviceLocator
    ) {
        $this->initializeCommandMap();
    }

    private function initializeCommandMap(): void
    {
        $dependencies = [
            'handleService',
            'outputService',
        ];

        $this->commandMap = [
            'commit:create' => [
                'class' => CommitCreateCommand::class,
                'dependencies' => $dependencies,
            ],
            'commit:delete' => [
                'class' => CommitDeleteCommand::class,
                'dependencies' => $dependencies,
            ],
            'commit:edit' => [
                'class' => CommitEditCommand::class,
                'dependencies' => $dependencies,
            ],
            'git:gitignore' => [
                'class' => GitGitignoreCommand::class,
            ],
            'task:archive' => [
                'class' => TaskArchiveCommand::class,
                'dependencies' => $dependencies,
            ],
            'task:delete' => [
                'class' => TaskDeleteCommand::class,
                'dependencies' => $dependencies,
            ],
            'task:list' => [
                'class' => TaskListCommand::class,
                'dependencies' => $dependencies,
            ],
            'task:start' => [
                'class' => TaskStartCommand::class,
                'dependencies' => $dependencies,
            ],
            'task:status' => [
                'class' => TaskStatusCommand::class,
                'dependencies' => $dependencies,
            ],
            'task:stop' => [
                'class' => TaskStopCommand::class,
                'dependencies' => $dependencies,
            ],
            'version:upgrade' => [
                'class' => VersionUpgradeCommand::class,
                'dependencies' => ['httpClient'],
            ],
        ];
    }

    public function get(string $name): Command
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        return $this->createCommand($name);
    }

    private function createCommand(string $name): Command
    {
        $config = $this->commandMap[$name];
        $dependencies = [];

        // Récupération des dépendances via le ServiceLocator
        foreach ($config['dependencies'] ?? [] as $serviceName) {
            $dependencies[] = $this->serviceLocator->get($serviceName);
        }

        // Création de l'instance avec les dépendances
        return new $config['class'](...$dependencies);
    }

    public function has(string $name): bool
    {
        return isset($this->commandMap[$name]);
    }

    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}