<?php

namespace Mediashare\Marathon\Composer;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Installer {
    public static function install(): void {
        $sourceDir = dirname(__DIR__, 2);
        $binDir = $sourceDir . '/bin';

        if (!is_dir($binDir) && !mkdir($binDir)):
            throw new ProcessFailedException(sprintf('Directory "%s" was not created', $binDir));
        endif;

        if (file_exists($vendorBin = $sourceDir . '/vendor/mediashare/marathon/bin/marathon')):
            $process = new Process(['cp', $vendorBin, $binDir . '/marathon']);
            $process->run();
            if (!$process->isSuccessful()):
                throw new ProcessFailedException($process);
            endif;
        endif;
    }
}