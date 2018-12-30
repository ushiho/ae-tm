<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FuelReconciliationRepository")
 */
class FuelReconciliation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $totalAmount;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $totalLitres;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $kilometrage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="fuelReconciliations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="fuelReconciliations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="fuelReconciliations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Vehicle", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Driver", inversedBy="fuelReconciliations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GasStation", inversedBy="reconciliations")
     */
    private $gasStation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="reconciliations")
     */
    private $invoice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPaid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $receiptNum;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mission", inversedBy="fuelReconciliations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mission;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function setTotalAmount($totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getTotalLitres()
    {
        return $this->totalLitres;
    }

    public function setTotalLitres($totalLitres): self
    {
        $this->totalLitres = $totalLitres;

        return $this;
    }

    public function getKilometrage()
    {
        return $this->kilometrage;
    }

    public function setKilometrage($kilometrage): self
    {
        $this->kilometrage = $kilometrage;

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

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getGasStation(): ?GasStation
    {
        return $this->gasStation;
    }

    public function setGasStation(?GasStation $gasStation): self
    {
        $this->gasStation = $gasStation;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getReceiptNum(): ?string
    {
        return $this->receiptNum;
    }

    public function setReceiptNum(string $receiptNum): self
    {
        $this->receiptNum = $receiptNum;

        return $this;
    }

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(?Mission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }
}
