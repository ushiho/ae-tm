<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentSupplierRepository")
 */
class PaymentSupplier
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
    private $datePayment;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $totalPricePaid;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $totalPriceToPay;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $remainingPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment", inversedBy="paymentVehicle")
     * @ORM\JoinColumn(nullable=false)
     */
    private $payment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Allocate", inversedBy="paymentSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $allocate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePayment(): ?\DateTimeInterface
    {
        return $this->datePayment;
    }

    public function setDatePayment(\DateTimeInterface $datePayment): self
    {
        $this->datePayment = $datePayment;

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

    public function getTotalPricePaid()
    {
        return $this->totalPricePaid;
    }

    public function setTotalPricePaid($totalPricePaid): self
    {
        $this->totalPricePaid = $totalPricePaid;

        return $this;
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

    public function getRemainingPrice()
    {
        return $this->remainingPrice;
    }

    public function setRemainingPrice($remainingPrice): self
    {
        $this->remainingPrice = $remainingPrice;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getAllocate(): ?Allocate
    {
        return $this->allocate;
    }

    public function setAllocate(?Allocate $allocate): self
    {
        $this->allocate = $allocate;

        return $this;
    }
}