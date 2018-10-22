<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=60, scale=2)
     */
    private $totalPriceToPay=0; # total remaining price

    /**
     * @ORM\Column(type="decimal", precision=60, scale=2)
     */
    private $totalPricePaid=0; # total amount paid = paymentDriever + paymentSupplier

    /**
     * @ORM\Column(type="decimal", precision=60, scale=2)
     */
    private $totalPrice=0; # tatal amount = driver salary * number of days + supplier salary * number of days

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentDriver", mappedBy="payment")
     */
    private $paymentDriver;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSupplier", mappedBy="payment")
     */
    private $paymentSupplier;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Mission", mappedBy="payment", cascade={"persist", "remove"})
     */
    private $mission;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $finished;

    public function __construct()
    {
        $this->paymentDriver = new ArrayCollection();
        $this->paymentSupplier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalPriceToPay()
    {
        return $this->totalPriceToPay;
    }

    public function setTotalPriceToPay($totalPriceToPay): self
    {
        $this->totalPriceToPay = $totalPriceToPay;

        return $this;
    }

    public function getTotalPricePaid()
    {
        return $this->totalPricePaid;
    }

    public function setTotalPricePaid($totalPricePaid): self
    {
        $this->totalPricePaid = $totalPricePaid;

        return $this;
    }

    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function setTotalPrice($totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return Collection|PaymentDriver[]
     */
    public function getPaymentDriver(): Collection
    {
        return $this->paymentDriver;
    }

    public function addPaymentDriver(PaymentDriver $paymentDriver): self
    {
        if (!$this->paymentDriver->contains($paymentDriver)) {
            $this->paymentDriver[] = $paymentDriver;
            $paymentDriver->setPayment($this);
        }

        return $this;
    }

    public function removePaymentDriver(PaymentDriver $paymentDriver): self
    {
        if ($this->paymentDriver->contains($paymentDriver)) {
            $this->paymentDriver->removeElement($paymentDriver);
            // set the owning side to null (unless already changed)
            if ($paymentDriver->getPayment() === $this) {
                $paymentDriver->setPayment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PaymentSupplier[]
     */
    public function getPaymentSupplier(): Collection
    {
        return $this->paymentSupplier;
    }

    public function addPaymentSupplier(PaymentSupplier $paymentSupplier): self
    {
        if (!$this->paymentSupplier->contains($paymentSupplier)) {
            $this->paymentSupplier[] = $paymentSupplier;
            $paymentSupplier->setPayment($this);
        }

        return $this;
    }

    public function removePaymentSupplier(PaymentSupplier $paymentSupplier): self
    {
        if ($this->paymentSupplier->contains($paymentSupplier)) {
            $this->paymentSupplier->removeElement($paymentSupplier);
            // set the owning side to null (unless already changed)
            if ($paymentSupplier->getPayment() === $this) {
                $paymentSupplier->setPayment(null);
            }
        }

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
        $newPayment = $mission === null ? null : $this;
        if ($newPayment !== $mission->getPayment()) {
            $mission->setPayment($newPayment);
        }

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(?bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function __clone() {
        $this->instance = ++self::$instances;
      }
}
