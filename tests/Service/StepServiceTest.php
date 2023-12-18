<?php

namespace Mediashare\Marathon\Tests\Service;

use Mediashare\Marathon\Entity\Step;
use Mediashare\Marathon\Exception\StrToTimeException;
use Mediashare\Marathon\Service\StepService;

class StepServiceTest extends AbstractServiceTestCase {
    private StepService $stepService;

    public function setUp(): void {
        parent::setUp();

        $this->stepService = new StepService();
    }

    public function testCreateWithoutDates(): void {
        $step = $this->stepService->create();

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNull($step->getEndDate());
    }

    public function testCreateWithCustomStartDate(): void {
        $customStartDate = strtotime('2023-01-01');
        $step = $this->stepService->create($customStartDate);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertEquals($customStartDate, $step->getStartDate());
        $this->assertNull($step->getEndDate());
    }

    public function testCreateWithEndDate(): void {
        $endDate = strtotime('2023-02-01');
        $step = $this->stepService->create(null, $endDate);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertEquals($endDate, $step->getEndDate());
    }

    public function testCreateStepWithCustomDates(): void {
        $startDate = (new \DateTime('-1 day'))->getTimestamp();
        $endDate = (new \DateTime())->getTimestamp();

        $step = $this->stepService->create($startDate, $endDate);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertEquals($startDate, $step->getStartDate());
        $this->assertEquals($endDate, $step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(86400, $step->getSeconds());
        $this->assertEquals('1d 00:00:00', $step->getDuration());
    }

    public function testCreateStepWithCustomDuration(): void {
        $startDate = (new \DateTime('-1 day'))->getTimestamp();
        $duration = '+2 day';
        $endDate = strtotime($duration, $startDate);

        $step = $this->stepService->createWithCustomDuration($duration, $startDate);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertEquals($startDate, $step->getStartDate());
        $this->assertEquals($endDate, $step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(172800, $step->getSeconds());
        $this->assertEquals('2d 00:00:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDuration(): void {
        $customDuration = '+5 minutes';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(300, $step->getSeconds());
        $this->assertEquals('00:05:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationComplexe(): void {
        $customDuration = '+1hour 5 minutes';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(3900, $step->getSeconds());
        $this->assertEquals('01:05:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationWeeksNormalizer(): void {
        $customDuration = '1w';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(604800, $step->getSeconds());
        $this->assertEquals('7d 00:00:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationDaysNormalizer(): void {
        $customDuration = '1d';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(86400, $step->getSeconds());
        $this->assertEquals('1d 00:00:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationHoursNormalizer(): void {
        $customDuration = '1h';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(3600, $step->getSeconds());
        $this->assertEquals('01:00:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationMinutes(): void {
        $customDuration = '1min';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(60, $step->getSeconds());
        $this->assertEquals('00:01:00', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationSeconds(): void {
        $customDuration = '1s';
        $step = $this->stepService->createWithCustomDuration($customDuration);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertNotNull($step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(1, $step->getSeconds());
        $this->assertEquals('00:00:01', $step->getDuration());
    }

    /**
     * @throws StrToTimeException
     */
    public function testCreateWithCustomDurationAndStartDate(): void {
        $customDuration = '+2 hours';
        $customStartDate = strtotime('2023-03-01');
        $step = $this->stepService->createWithCustomDuration($customDuration, $customStartDate);

        $this->assertInstanceOf(Step::class, $step);
        $this->assertEquals($customStartDate, $step->getStartDate());
        $this->assertNotNull($step->getEndDate());
        $this->assertGreaterThan($step->getStartDate(), $step->getEndDate());
        $this->assertEquals(7200, $step->getSeconds());
        $this->assertEquals('02:00:00', $step->getDuration());
    }

    public function testStrToTimeException(): void {
        $this->expectException(StrToTimeException::class);

        // Pass an invalid duration format to trigger StrToTimeException
        $this->stepService->createWithCustomDuration('invalid_duration_format');
    }
}