<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TimerCollection;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Timer;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TimerNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class TimerService {
    private SerializerService $serializerService;
    private Filesystem $filesystem;

    private Config $config;
    private Timer|null $timer = null;

    public function __construct(
        private StepService $stepService,
    ) {
        $this->serializerService = new SerializerService();
        $this->filesystem = new Filesystem();
    }

    public function setConfig(Config $config): self {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTimers(): TimerCollection {
        $timerCollection = new TimerCollection();
        foreach (glob($this->config->getTimerDirectory() . '/*') as $filepath):
            $timerCollection->add($this->serializerService->read($filepath, Timer::class));
        endforeach;

        return $timerCollection;
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTimer(bool $createItIfNotExist = true): Timer {
        if ($this->timer instanceof Timer):
            return $this->timer;
        endif;

        $timerExist = $this->filesystem->exists($filepath = $this->getTimerFilepath());
        if (!$timerExist && $createItIfNotExist):
            return $this->setTimer($this->createTimer())->getTimer();
        elseif (!$timerExist):
            throw new TimerNotFoundException();
        endif;

        return $this
            ->setTimer($this->serializerService->read($filepath, Timer::class))
            ->getTimer();
    }

    public function setTimer(Timer $timer): self {
        $this->timer = $timer;

        return $this;
    }

    public function createTimer(array $data = []): Timer {
        /** @var Timer $timer */
        $timer = $this->serializerService->arrayToEntity($data, Timer::class);

        if (!$timer->getId()):
            $timer->setId($this->config->getTimerId() ?? (new \DateTime())->format('YmdHis'));
        endif;

        if ($timer->isRun() && !$timer->getSteps()?->last()?->getEndDate()):
            $timer->addStep($this->stepService->createStep());
        endif;

        $this->serializerService->writeTimer($this->getTimerFilepath(), $timer);

        return $timer;
    }

    public function start(
        string|false $name = false,
        string|false $duration = false,
    ): self {
        $timer = $this->getTimer()
            ->setRun(true)
            ->setName($name !== false ? $name : $this->getTimer()->getName());

        if ($duration):
            $firstStep = $timer->getSteps()->first();
            $timer->getSteps()->clear();
            $timer->addStep($this
                ->stepService
                ->createStepWithCustomDuration(
                    $duration,
                    $firstStep?->getStartDate(),
                )
            );
        endif;

        if (!$timer->getStartDate() || !($lastStep = $timer->getSteps()?->last()) || $lastStep->getEndDate()):
            $timer
                ->addStep(
                    $this->stepService->createStep()
                );
        endif;

        return $this->setTimer($timer);
    }

    public function stop(): self {
        $timer = $this
            ->getTimer(createItIfNotExist: false)
            ->setRun(false);

        if (($lastStep = $timer->getSteps()?->last()) && !$lastStep->getEndDate()):
            $timer
                ->getSteps()
                ->offsetSet(
                    $timer->getSteps()->getKey($lastStep),
                    $lastStep->setEndDate((new \DateTime())->getTimestamp()),
                );
        endif;

        return $this->setTimer($timer);
    }

    public function archive(): self {
        $this
            ->stop()
            ->getTimer(createItIfNotExist: false)
            ->setArchived(true);

        return $this;
    }

    public function delete(): self {
        $this->filesystem
            ->remove($this->getTimerFilepath())
        ;

        return $this;
    }

    /**
     * @throws TimerNotFoundException
     */
    public function getTimerFilepath(): string {
        if (!$this->getConfig()->getTimerId()):
            throw new TimerNotFoundException();
        endif;

        return $this->getConfig()->getTimerDirectory().DIRECTORY_SEPARATOR. $this->getConfig()->getTimerId().'.json';
    }
}