<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
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
    private $totalAmounts;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $totalLitres;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPaid;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FuelReconciliation", mappedBy="invoice")
     */
    private $reconciliations;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    public function __construct()
    {
        $this->reconciliations = new ArrayCollection();
    }

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

    public function getTotalAmounts()
    {
        return $this->totalAmounts;
    }

    public function setTotalAmounts($totalAmounts): self
    {
        $this->totalAmounts = $totalAmounts;

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

    public function getIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * @return Collection|FuelReconciliation[]
     */
    public function getReconciliations(): Collection
    {
        return $this->reconciliations;
    }

    public function addReconciliation(FuelReconciliation $reconciliation): self
    {
        if (!$this->reconciliations->contains($reconciliation)) {
            $this->reconciliations[] = $reconciliation;
            $reconciliation->setInvoice($this);
        }

        return $this;
    }

    public function removeReconciliation(FuelReconciliation $reconciliation): self
    {
        if ($this->reconciliations->contains($reconciliation)) {
            $this->reconciliations->removeElement($reconciliation);
            // set the owning side to null (unless already changed)
            if ($reconciliation->getInvoice() === $this) {
                $reconciliation->setInvoice(null);
            }
        }

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }
}
