<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VehicleRepository")
 */
class Vehicle
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
    private $reg;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mileage;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $brand;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Allocate", mappedBy="vehicle", cascade={"persist", "remove"})
     */
    private $allocate;


    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReg(): ?string
    {
        return $this->reg;
    }

    public function setReg(string $reg): self
    {
        $this->reg = $reg;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): self
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getAllocate(): ?Allocate
    {
        return $this->allocate;
    }

    public function setAllocate(Allocate $allocate): self
    {
        $this->allocate = $allocate;

        // set the owning side of the relation if necessary
        if ($this !== $allocate->getVehicle()) {
            $allocate->setVehicle($this);
        }

        return $this;
    }

}
