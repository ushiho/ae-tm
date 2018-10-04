<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupplierRepository")
 */
class Supplier
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adress;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSupplier", mappedBy="supplier")
     */
    private $paymentSuppliers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Allocate", mappedBy="supplier")
     */
    private $allocates;

    public function __construct()
    {
        $this->paymentSuppliers = new ArrayCollection();
        $this->allocates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): self
    {
        $this->adress = $adress;

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
            $paymentSupplier->setSupplier($this);
        }

        return $this;
    }

    public function removePaymentSupplier(PaymentSupplier $paymentSupplier): self
    {
        if ($this->paymentSuppliers->contains($paymentSupplier)) {
            $this->paymentSuppliers->removeElement($paymentSupplier);
            // set the owning side to null (unless already changed)
            if ($paymentSupplier->getSupplier() === $this) {
                $paymentSupplier->setSupplier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Allocate[]
     */
    public function getAllocates(): Collection
    {
        return $this->allocates;
    }

    public function addAllocate(Allocate $allocate): self
    {
        if (!$this->allocates->contains($allocate)) {
            $this->allocates[] = $allocate;
            $allocate->setSupplier($this);
        }

        return $this;
    }

    public function removeAllocate(Allocate $allocate): self
    {
        if ($this->allocates->contains($allocate)) {
            $this->allocates->removeElement($allocate);
            // set the owning side to null (unless already changed)
            if ($allocate->getSupplier() === $this) {
                $allocate->setSupplier(null);
            }
        }

        return $this;
    }
}
