<?php

namespace Mediashare\Marathon;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void {
        $container
            ->registerForAutoconfiguration(CommandLoaderInterface::class)
            ->addTag('console.command_loader');
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/marathon_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/marathon.log';
    }
}