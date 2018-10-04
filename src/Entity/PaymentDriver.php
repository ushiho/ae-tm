<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentDriverRepository")
 */
class PaymentDriver
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
     * @ORM\Column(type="decimal", precision=50, scale=27)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $totalPrice;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $pricePaid;

    /**
     * @ORM\Column(type="smallint")
     */
    private $period;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Driver", inversedBy="paymentDrivers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $remainingPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment", inversedBy="paymentDriver")
     * @ORM\JoinColumn(nullable=false)
     */
    private $payment;

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

    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function setTotalPrice($totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getPricePaid()
    {
        return $this->pricePaid;
    }

    public function setPricePaid($pricePaid): self
    {
        $this->pricePaid = $pricePaid;

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

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

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
}