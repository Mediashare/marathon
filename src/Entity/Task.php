<?php
namespace Mediashare\Marathon\Entity;

use Mediashare\Marathon\Collection\CommitCollection;
use Mediashare\Marathon\Collection\StepCollection;
use Mediashare\Marathon\Trait\EntityDateTimeTrait;
use Mediashare\Marathon\Trait\EntityDurationTrait;
use Mediashare\Marathon\Trait\EntityUnserializerTrait;

class Task {
    use EntityDateTimeTrait;
    use EntityDurationTrait;
    use EntityUnserializerTrait;

    private string|null $id = null;
    private string|null $name = null;
    private bool $run = true;
    private bool $archived = false;

    /** @var CommitCollection<Commit> */
    private CommitCollection $commits;
    /** @var StepCollection<Step> */
    private StepCollection $steps;

    /**
     * @description Remaining seconds to complete the task.
     */
    private int|null $remaining = null;

    private int|null $lastUpdateDate = null;

    public function __construct() {
        $this
            ->setCommits(new CommitCollection())
            ->setSteps(new StepCollection())
        ;
    }

    public function setId(string $id): self {
        $this->id = $id;

        return $this;
    }

    public function getId(): string|null {
        return $this->id;
    }

    public function setName(string|null $name): self {
        $this->name = $name;

        return $this;
    }

    public function getName(): string|null {
        return $this->name;
    }

    public function setRemaining(int|null $remaining): self {
        $this->remaining = $remaining;

        return $this;
    }

    public function getRemaining(): int|null {
        return $this->remaining;
    }

    public function setRun(bool $run): self {
        $this->run = $run;

        return $this;
    }

    public function isRun(): bool {
        return $this->run;
    }

    public function setArchived(bool $archived): self {
        $this->archived = $archived;

        return $this;
    }

    public function isArchived(): bool {
        return $this->archived;
    }

    public function setCommits(CommitCollection $commits): self {
        $this->commits = $commits;

        return $this;
    }

    public function getCommits(): CommitCollection {
        return $this->commits;
    }

    public function addCommit(Commit $commit): self {
        if (!$this->getCommits()->contains($commit)):
            $this->getCommits()->add($commit);
        endif;

        return $this;
    }

    public function removeCommit(Commit $commit): self {
        if ($this->getCommits()->contains($commit)):
            $this->getCommits()->remove($commit);
        endif;

        return $this;
    }

    /**
     * @param StepCollection<Step> $steps
     * @return $this
     */
    public function setSteps(StepCollection $steps): self {
        $this->steps = $steps;

        return $this;
    }

    /**
     * @return StepCollection<Step>
     */
    public function getSteps(): StepCollection {
        return $this->steps;
    }

    public function addStep(Step $step): self {
        if (!$this->getSteps()->contains($step)):
            $this->getSteps()->add($step);
        endif;

        return $this;
    }

    public function setLastUpdateDate(int|null $lastUpdateDate): self {
        $this->lastUpdateDate = $lastUpdateDate;

        return $this;
    }

    public function getLastUpdateDate(): int|null {
        return $this->lastUpdateDate;
    }
}
