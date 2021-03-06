<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MissionRepository")
 */
class Mission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThan(propertyPath="startDate", message="The end date should be greater than start date")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="missions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="mission", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="boolean")
     */
    private $finished;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Payment", inversedBy="mission", cascade={"persist", "remove"})
     */
    private $payment;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Allocate", inversedBy="mission", cascade={"persist", "remove"})
     */
    private $allocate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Driver", inversedBy="missions", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid()
     */
    private $driver;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FuelReconciliation", mappedBy="mission")
     */
    private $fuelReconciliations;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $salaire;

    /**
     * @ORM\Column(type="integer")
     */
    private $periodOfWork;

    public function __construct()
    {
        $this->fuelReconciliations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getProject(): ?Project
    {
        return  $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return  $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getAllocate(): ?Allocate
    {
        return  $this->allocate;
    }

    public function setAllocate(?Allocate $allocate): self
    {
        $this->allocate = $allocate;

        return $this;
    }

    public function getDriver(): ?Driver
    {
        return  $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return Collection|FuelReconciliation[]
     */
    public function getFuelReconciliations(): Collection
    {
        return $this->fuelReconciliations;
    }

    public function addFuelReconciliation(FuelReconciliation $fuelReconciliation): self
    {
        if (!$this->fuelReconciliations->contains($fuelReconciliation)) {
            $this->fuelReconciliations[] = $fuelReconciliation;
            $fuelReconciliation->setMission($this);
        }

        return $this;
    }

    public function removeFuelReconciliation(FuelReconciliation $fuelReconciliation): self
    {
        if ($this->fuelReconciliations->contains($fuelReconciliation)) {
            $this->fuelReconciliations->removeElement($fuelReconciliation);
            // set the owning side to null (unless already changed)
            if ($fuelReconciliation->getMission() === $this) {
                $fuelReconciliation->setMission(null);
            }
        }

        return $this;
    }

    public function getSalaire()
    {
        return $this->salaire;
    }

    public function setSalaire($salaire): self
    {
        $this->salaire = $salaire;

        return $this;
    }

    public function getPeriodOfWork(): ?int
    {
        return $this->periodOfWork;
    }

    public function setPeriodOfWork(int $periodOfWork): self
    {
        $this->periodOfWork = $periodOfWork;

        return $this;
    }
}
