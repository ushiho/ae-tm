<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepartmentRepository")
 */
class Department
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
     * @ORM\Column(type="string", length=255)
     */
    private $adress;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mission", mappedBy="department")
     */
    private $missions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FuelReconciliation", mappedBy="department", orphanRemoval=true)
     */
    private $fuelReconciliations;

    public function __construct()
    {
        $this->missions = new ArrayCollection();
        $this->fuelReconciliations = new ArrayCollection();
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

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * @return Collection|Mission[]
     */
    public function getMissions(): Collection
    {
        return $this->missions;
    }

    public function addMission(Mission $mission): self
    {
        if (!$this->missions->contains($mission)) {
            $this->missions[] = $mission;
            $mission->setDepartment($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): self
    {
        if ($this->missions->contains($mission)) {
            $this->missions->removeElement($mission);
            // set the owning side to null (unless already changed)
            if ($mission->getDepartment() === $this) {
                $mission->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FuelReconciliation[]
     */
    public function getFuelReconciliations(): Collection
    {
        return $this->fuelReconciliations;
    }

    public function addFuelReconciliation(FuelReconciliation $fuelReconciliation): self
    {
        if (!$this->fuelReconciliations->contains($fuelReconciliation)) {
            $this->fuelReconciliations[] = $fuelReconciliation;
            $fuelReconciliation->setDepartment($this);
        }

        return $this;
    }

    public function removeFuelReconciliation(FuelReconciliation $fuelReconciliation): self
    {
        if ($this->fuelReconciliations->contains($fuelReconciliation)) {
            $this->fuelReconciliations->removeElement($fuelReconciliation);
            // set the owning side to null (unless already changed)
            if ($fuelReconciliation->getDepartment() === $this) {
                $fuelReconciliation->setDepartment(null);
            }
        }

        return $this;
    }

}
