<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GasStationRepository")
 */
class GasStation
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FuelReconciliation", mappedBy="gasStation")
     */
    private $reconciliations;

    public function __construct()
    {
        $this->reconciliations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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
            $reconciliation->setGasStation($this);
        }

        return $this;
    }

    public function removeReconciliation(FuelReconciliation $reconciliation): self
    {
        if ($this->reconciliations->contains($reconciliation)) {
            $this->reconciliations->removeElement($reconciliation);
            // set the owning side to null (unless already changed)
            if ($reconciliation->getGasStation() === $this) {
                $reconciliation->setGasStation(null);
            }
        }

        return $this;
    }
}
