<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Service\TimestampService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Mediashare\Marathon\Service\OutputService;

class OutputServiceTest extends KernelTestCase {
    private OutputService $outputService;
    private TimestampService $timestampService;

    public function setUp(): void {
        parent::setUp();

        $this->outputService = new OutputService($this->timestampService = new TimestampService());
    }

    public function testGetMaxWidthOfColumn(): void
    {
        $actualWidthOfColumn = stripos(PHP_OS_FAMILY, 'WIN') === 0
            ? (int) shell_exec('powershell -Command "&{(Get-Host).UI.RawUI.WindowSize.Width}"')
            : (int) shell_exec('tput cols')
        ;

        $expected = $actualWidthOfColumn - (int) ($actualWidthOfColumn / 5);

        $this->outputService->setMaxWidthOfColumn();

        $this->assertEquals((int) $expected, $this->outputService->getMaxWidthOfColumn());
    }
}
