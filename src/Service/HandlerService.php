<?php

namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Collection\TimerCollection;
use Mediashare\Marathon\Entity\Commit;
use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Timer;
use Mediashare\Marathon\Exception\CommitNotFoundException;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Exception\TimerNotFoundException;

class HandlerService {
    private Config $config;
    private Timer $timer;

    public function __construct(
        private ConfigService $configService,
        private TimerService $timerService,
        private CommitService $commitService,
        private SerializerService $serializerService,
    ) {}

    public function setConfig(
        string|null $configPath = null,
        string|null $dateTimeFormat = null,
        string|null $timerDirectory = null,
        string|null $timerId = null,
    ): self {
        $this->config = $this->configService->write(
            $configPath,
            $dateTimeFormat,
            $timerDirectory,
            $timerId,
        )->getConfig();

        return $this;
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    public function updateCurrentTrackingId(): self {
        $lastTimerId = $this->configService->getLastTimerId(
            $this->config->getTimerDirectory(),
        );

        $this->configService->write(
            timerId: $this->getConfig()->getTimerId() === $lastTimerId
                ? (new \DateTime())->format('YmdHis')
                : $lastTimerId
            ,
        );

        return $this;
    }

    private function setTimer(Timer $timer): self {
        $this->timer = $timer;

        return $this;
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTimer(): Timer {
        return $this->timer
            ?? $this->timerService
                ->setConfig($this->getConfig())
                ->getTimer()
            ;
    }

    /**
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function getTimers(): TimerCollection {
        return $this->timerService
            ->setConfig($this->getConfig())
            ->getTimers();
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function start(
        string|false $name = false,
        string|false $duration = false,
    ): self {
        return $this->setTimer(
            $this->timerService
                ->setConfig($this->getConfig())
                ->start($name, $duration)
                ->getTimer()
        );
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function stop(): self {
        return $this->setTimer(
            $this->timerService
                ->setConfig($this->getConfig())
                ->stop()
                ->getTimer()
        );
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function archive(): self {
        return $this->setTimer(
            $this->timerService
                ->setConfig($this->getConfig())
                ->archive()
                ->getTimer()
        );
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function delete(): self {
        $timerService = $this->timerService->setConfig($this->getConfig());

        $this->setTimer($timerService->getTimer(createItIfNotExist: false));

        $timerService->delete();

        return $this;
    }

    /**
     * @throws CommitNotFoundException
     * @throws FileNotFoundException
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     */
    public function commit(
        string|false $id = false,
        string|false $message = false,
        string|false $duration = false,
        bool|null $isDelete = false,
    ): self {
        if ($id === false):
            $this->commitService
                ->setTimer($this->getTimer())
                ->create(
                    $message,
                    $duration
                );
        elseif ($isDelete === true):
            $this->commitService
                ->setTimer($this->getTimer())
                ->delete($id);
        else:
            $this->commitService
                ->setTimer($this->getTimer())
                ->edit(
                    $id,
                    $message,
                    $duration
                );
        endif;

        return $this->setTimer($this->commitService->getTimer());
    }

    /**
     * @throws TimerNotFoundException
     * @throws JsonDecodeException
     * @throws FileNotFoundException
     */
    public function write(): self {
        $this->serializerService
            ->writeTimer(
                $this->timerService->getTimerFilepath(),
                $this->getTimer()
            );

        return $this;
    }
}