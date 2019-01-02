<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


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
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPricePaid; # total amount paid = paymentDriever + paymentSupplier

    /**
     * @ORM\Column(type="decimal", precision=60, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPrice; # 

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentDriver", mappedBy="payment", cascade={"persist", "remove"})
     */
    private $paymentDriver;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSupplier", mappedBy="payment", cascade={"persist", "remove"})
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

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPriceToPayToDriver;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPriceToPayToSupplier;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $remainingPrice;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $remainigPriceToSupplier;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $remainingPriceToDriver;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPricePaidToSupplier;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPricePaidToDriver;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value = 0)
     */
    private $totalDaysToPay;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value = 0)
     */
    private $totalDaysPaid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(value = 0)
     */
    private $remainingDays;

    public function __construct()
    {
        $this->paymentDriver = new ArrayCollection();
        $this->paymentSupplier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTotalPriceToPayToDriver()
    {
        return $this->totalPriceToPayToDriver;
    }

    public function setTotalPriceToPayToDriver($totalPriceToPayToDriver): self
    {
        $this->totalPriceToPayToDriver = $totalPriceToPayToDriver;

        return $this;
    }

    public function getTotalPriceToPayToSupplier()
    {
        return $this->totalPriceToPayToSupplier;
    }

    public function setTotalPriceToPayToSupplier($totalPriceToPayToSupplier): self
    {
        $this->totalPriceToPayToSupplier = $totalPriceToPayToSupplier;

        return $this;
    }

    public function getRemainingPrice()
    {
        return $this->remainingPrice;
    }

    public function setRemainingPrice($remainingPrice): self
    {
        $this->remainingPrice = $remainingPrice;

        return $this;
    }

    public function getRemainigPriceToSupplier()
    {
        return $this->remainigPriceToSupplier;
    }

    public function setRemainigPriceToSupplier($remainigPriceToSupplier): self
    {
        $this->remainigPriceToSupplier = $remainigPriceToSupplier;

        return $this;
    }

    public function getRemainingPriceToDriver()
    {
        return $this->remainingPriceToDriver;
    }

    public function setRemainingPriceToDriver($remainingPriceToDriver): self
    {
        $this->remainingPriceToDriver = $remainingPriceToDriver;

        return $this;
    }

    public function getTotalPricePaidToSupplier()
    {
        return $this->totalPricePaidToSupplier;
    }

    public function setTotalPricePaidToSupplier($totalPricePaidToSupplier): self
    {
        $this->totalPricePaidToSupplier = $totalPricePaidToSupplier;

        return $this;
    }

    public function getTotalPricePaidToDriver()
    {
        return $this->totalPricePaidToDriver;
    }

    public function setTotalPricePaidToDriver($totalPricePaidToDriver): self
    {
        $this->totalPricePaidToDriver = $totalPricePaidToDriver;

        return $this;
    }

    public function __toString()
    {
        return '';
    }

    public function getTotalDaysToPay(): ?int
    {
        return $this->totalDaysToPay;
    }

    public function setTotalDaysToPay(int $totalDaysToPay): self
    {
        $this->totalDaysToPay = $totalDaysToPay;

        return $this;
    }

    public function getTotalDaysPaid(): ?int
    {
        return $this->totalDaysPaid;
    }

    public function setTotalDaysPaid(int $totalDaysPaid): self
    {
        $this->totalDaysPaid = $totalDaysPaid;

        return $this;
    }

    public function getRemainingDays(): ?int
    {
        return $this->remainingDays;
    }

    public function setRemainingDays(?int $remainingDays): self
    {
        $this->remainingDays = $remainingDays;

        return $this;
    }
}
