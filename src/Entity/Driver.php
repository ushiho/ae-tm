<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DriverRepository")
 */
class Driver
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
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numberPhone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $licenceNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentDriver", mappedBy="driver")
     */
    private $paymentDrivers;

    /**
     * @ORM\Column(type="smallint")
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mission", mappedBy="driver")
     */
    private $missions;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\VehicleType", inversedBy="drivers")
     */
    private $vehicleType;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $salaire;

    /**
     * @ORM\Column(type="smallint")
     */
    private $periodOfTravel;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=2)
     */
    private $salairePerDay;


    public function __construct()
    {
        $this->paymentDrivers = new ArrayCollection();
        $this->missions = new ArrayCollection();
        $this->vehicleType = new ArrayCollection();
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

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getNumberPhone(): ?string
    {
        return $this->numberPhone;
    }

    public function setNumberPhone(string $numberPhone): self
    {
        $this->numberPhone = $numberPhone;

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

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

    public function getLicenceNumber(): ?string
    {
        return $this->licenceNumber;
    }

    public function setLicenceNumber(string $licenceNumber): self
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    /**
     * @return Collection|PaymentDriver[]
     */
    public function getPaymentDrivers(): Collection
    {
        return $this->paymentDrivers;
    }

    public function addPaymentDriver(PaymentDriver $paymentDriver): self
    {
        if (!$this->paymentDrivers->contains($paymentDriver)) {
            $this->paymentDrivers[] = $paymentDriver;
            $paymentDriver->setDriver($this);
        }

        return $this;
    }

    public function removePaymentDriver(PaymentDriver $paymentDriver): self
    {
        if ($this->paymentDrivers->contains($paymentDriver)) {
            $this->paymentDrivers->removeElement($paymentDriver);
            // set the owning side to null (unless already changed)
            if ($paymentDriver->getDriver() === $this) {
                $paymentDriver->setDriver(null);
            }
        }

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(int $gender): self
    {
        $this->gender = $gender;

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
            $mission->setDriver($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): self
    {
        if ($this->missions->contains($mission)) {
            $this->missions->removeElement($mission);
            // set the owning side to null (unless already changed)
            if ($mission->getDriver() === $this) {
                $mission->setDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VehicleType[]
     */
    public function getVehicleType(): Collection
    {
        return $this->vehicleType;
    }

    public function addVehicleType(VehicleType $vehicleType): self
    {
        if (!$this->vehicleType->contains($vehicleType)) {
            $this->vehicleType[] = $vehicleType;
        }

        return $this;
    }

    public function removeVehicleType(VehicleType $vehicleType): self
    {
        if ($this->vehicleType->contains($vehicleType)) {
            $this->vehicleType->removeElement($vehicleType);
        }

        return $this;
    }

    public function getSalaire()
    {
        return $this->salaire;
    }

    public function setSalaire($salaire): self
    {
        $this->salaire = $salaire;

        return $this;
    }

    public function getPeriodOfTravel(): ?int
    {
        return $this->periodOfTravel;
    }

    public function setPeriodOfTravel(int $periodOfTravel): self
    {
        $this->periodOfTravel = $periodOfTravel;

        return $this;
    }

    public function getSalairePerDay()
    {
        return $this->salairePerDay;
    }

    public function setSalairePerDay($salairePerDay): self
    {
        $this->salairePerDay = $salairePerDay;

        return $this;
    }

}
