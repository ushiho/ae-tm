<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AllocateRepository")
 */
class Allocate
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
     * @ORM\Column(type="smallint")
     */
    private $period;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\Type(type="decimal")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", inversedBy="allocates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSupplier", mappedBy="allocate")
     */
    private $paymentSuppliers;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="boolean")
     */
    private $withDriver;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Vehicle", inversedBy="allocate", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Mission", mappedBy="allocate", cascade={"persist", "remove"})
     */
    private $mission;

    public function __construct()
    {
        $this->paymentSuppliers = new ArrayCollection();
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

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    
    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|PaymentSupplier[]
     */
    public function getPaymentSuppliers(): Collection
    {
        return $this->paymentSuppliers;
    }

    public function addPaymentSupplier(PaymentSupplier $paymentSupplier): self
    {
        if (!$this->paymentSuppliers->contains($paymentSupplier)) {
            $this->paymentSuppliers[] = $paymentSupplier;
            $paymentSupplier->setAllocate($this);
        }

        return $this;
    }

    public function removePaymentSupplier(PaymentSupplier $paymentSupplier): self
    {
        if ($this->paymentSuppliers->contains($paymentSupplier)) {
            $this->paymentSuppliers->removeElement($paymentSupplier);
            // set the owning side to null (unless already changed)
            if ($paymentSupplier->getAllocate() === $this) {
                $paymentSupplier->setAllocate(null);
            }
        }

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

    public function getWithDriver(): ?bool
    {
        return $this->withDriver;
    }

    public function setWithDriver(bool $withDriver): self
    {
        $this->withDriver = $withDriver;

        return $this;
    }

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(?Mission $mission): self
    {
        $this->mission = $mission;

        // set (or unset) the owning side of the relation if necessary
        $newAllocate = $mission === null ? null : $this;
        if ($newAllocate !== $mission->getAllocate()) {
            $mission->setAllocate($newAllocate);
        }

        return $this;
    }
}
