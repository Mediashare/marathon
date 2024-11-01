<?php

namespace Mediashare\Marathon\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TaskStartCommandTest extends AbstractCommandTestCase
{
    public function setUp(): void {
        parent::setUp();
    }

    public function testTaskStart(): void {
        $command = $this->application->find('task:start');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--name' => 'test_name',
            '--config-path' => $this->configPath,
            '--config-task-dir' => $this->taskDirectory,
        ]);

        $output = $commandTester->getDisplay();
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('🏃', $output);
        $this->assertStringContainsString('test_name', $output);
    }

    public function testDurationSuccess(): void {
        $command = $this->application->find('task:start');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--name' => 'test_name',
            '--config-path' => $this->configPath,
            '--config-task-dir' => $this->taskDirectory,
            '--duration' => '1h 10min 20s',
        ]);

        $output = $commandTester->getDisplay();
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('🏃', $output);
        $this->assertStringContainsString('test_name', $output);
        $this->assertStringContainsString('1h 10min', $output);
    }

    public function testRemainingSuccess(): void {
        $command = $this->application->find('task:start');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--name' => 'test_name',
            '--config-path' => $this->configPath,
            '--config-task-dir' => $this->taskDirectory,
            '--remaining' => '3h 20min 10s',
        ]);

        $output = $commandTester->getDisplay();
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('🏃', $output);
        $this->assertStringContainsString('test_name', $output);
        $this->assertStringContainsString('🏋️ 3h 20min', $output);
    }

    public function testDurationError(): void {
        $command = $this->application->find('task:start');
        $commandTester = new CommandTester($command);

        $status = $commandTester->execute([
            '--duration' => 'error_duration',
            '--config-path' => $this->configPath,
            '--config-task-dir' => $this->taskDirectory,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertEquals(Command::FAILURE, $status);
        $this->assertStringContainsString('Duration set [error_duration] is not in correct format.', $output);
    }

    public function testRemainingError(): void {
        $command = $this->application->find('task:start');
        $commandTester = new CommandTester($command);

        $status = $commandTester->execute([
            '--remaining' => 'error_remaining',
            '--config-path' => $this->configPath,
            '--config-task-dir' => $this->taskDirectory,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertEquals(Command::FAILURE, $status);
        $this->assertStringContainsString('Remaining set [error_remaining] is not in correct format.', $output);
    }

    /**
     * @dataProvider getAliases
     */
    public function testAliases(string $alias): void {
        $command = $this->application->find($alias);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--name' => 'test_name',
            '--config-path' => $this->configPath,
            '--config-task-dir' => $this->taskDirectory,
        ]);

        $output = $commandTester->getDisplay();
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('🏃', $output);
        $this->assertStringContainsString('test_name', $output);
    }

    private function getAliases(): array {
        return [['task:start'], ['start'], ['run'], ['t:start'], ['task']];
    }
}