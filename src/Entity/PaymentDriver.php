<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $price; // amount paid in this date

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     * @Assert\GreaterThan(value = 0)
     */
    private $totalPrice; // number of days * salary per day, calculated in creating mission process

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Driver", inversedBy="paymentDrivers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment", inversedBy="paymentDriver")
     * @ORM\JoinColumn(nullable=false)
     */
    private $payment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value = 0)
     */
    private $daysToPay;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value = 0)
     */
    private $daysPaid;

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

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getDaysToPay(): ?int
    {
        return $this->daysToPay;
    }

    public function setDaysToPay(int $daysToPay): self
    {
        $this->daysToPay = $daysToPay;

        return $this;
    }

    public function getDaysPaid(): ?int
    {
        return $this->daysPaid;
    }

    public function setDaysPaid(int $daysPaid): self
    {
        $this->daysPaid = $daysPaid;

        return $this;
    }
}
