<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VehicleRepository")
 *  @UniqueEntity(
 *   fields={"matricule"},
 *   message=": There is a vahicle with this number!"
 * )
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
    private $brand;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Allocate", mappedBy="vehicle", cascade={"persist", "remove"})
     */
    private $allocate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\VehicleType", inversedBy="vehicles", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $matricule;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    private $state;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?VehicleType
    {
        return $this->type;
    }

    public function setType(?VehicleType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }
}
