<?php

namespace App\Entity;

use App\Repository\ElementsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ElementsRepository::class)
 */
class Elements
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $garage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $parking;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $basement;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $garden;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $basementParking;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $parentalSuite;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $furniture;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $poll;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $teracce;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mezzanine;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $storage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $veranda;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $unfurnished;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $bathroom;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $separateWc;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $wifi;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $elevator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $other;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\OneToMany(targetEntity=Housing::class, mappedBy="element")
     */
    private $apartments;

    public function __construct()
    {
        $this->apartments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'ID - ' . $this->id .' '. $this->other .' '. $this->note;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGarage(): ?bool
    {
        return $this->garage;
    }

    public function setGarage(bool $garage): self
    {
        $this->garage = $garage;

        return $this;
    }

    public function getParking(): ?bool
    {
        return $this->parking;
    }

    public function setParking(bool $parking): self
    {
        $this->parking = $parking;

        return $this;
    }

    public function getBasement(): ?bool
    {
        return $this->basement;
    }

    public function setBasement(bool $basement): self
    {
        $this->basement = $basement;

        return $this;
    }

    public function getGarden(): ?bool
    {
        return $this->garden;
    }

    public function setGarden(bool $garden): self
    {
        $this->garden = $garden;

        return $this;
    }

    public function getBasementParking(): ?bool
    {
        return $this->basementParking;
    }

    public function setBasementParking(bool $basementParking): self
    {
        $this->basementParking = $basementParking;

        return $this;
    }

    public function getParentalSuite(): ?bool
    {
        return $this->parentalSuite;
    }

    public function setParentalSuite(bool $parentalSuite): self
    {
        $this->parentalSuite = $parentalSuite;

        return $this;
    }

    public function getFurniture(): ?bool
    {
        return $this->furniture;
    }

    public function setFurniture(bool $furniture): self
    {
        $this->furniture = $furniture;

        return $this;
    }

    public function getPoll(): ?bool
    {
        return $this->poll;
    }

    public function setPoll(bool $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    public function getTeracce(): ?bool
    {
        return $this->teracce;
    }

    public function setTeracce(bool $teracce): self
    {
        $this->teracce = $teracce;

        return $this;
    }

    public function getMezzanine(): ?bool
    {
        return $this->mezzanine;
    }

    public function setMezzanine(bool $mezzanine): self
    {
        $this->mezzanine = $mezzanine;

        return $this;
    }

    public function getStorage(): ?bool
    {
        return $this->storage;
    }

    public function setStorage(bool $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    public function getVeranda(): ?bool
    {
        return $this->veranda;
    }

    public function setVeranda(bool $veranda): self
    {
        $this->veranda = $veranda;

        return $this;
    }

    public function getUnfurnished(): ?bool
    {
        return $this->unfurnished;
    }

    public function setUnfurnished(bool $unfurnished): self
    {
        $this->unfurnished = $unfurnished;

        return $this;
    }

    public function getBathroom(): ?bool
    {
        return $this->bathroom;
    }

    public function setBathroom(bool $bathroom): self
    {
        $this->bathroom = $bathroom;

        return $this;
    }

    public function getSeparateWc(): ?bool
    {
        return $this->separateWc;
    }

    public function setSeparateWc(bool $separateWc): self
    {
        $this->separateWc = $separateWc;

        return $this;
    }

    public function getWifi(): ?bool
    {
        return $this->wifi;
    }

    public function setWifi(bool $wifi): self
    {
        $this->wifi = $wifi;

        return $this;
    }

    public function getElevator(): ?bool
    {
        return $this->elevator;
    }

    public function setElevator(bool $elevator): self
    {
        $this->elevator = $elevator;

        return $this;
    }

    public function getOther(): ?string
    {
        return $this->other;
    }

    public function setOther(string $other): self
    {
        $this->other = $other;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return Collection|Housing[]
     */
    public function getApartments(): Collection
    {
        return $this->apartments;
    }

    public function addApartment(Housing $apartment): self
    {
        if (!$this->apartments->contains($apartment)) {
            $this->apartments[] = $apartment;
            $apartment->setElement($this);
        }

        return $this;
    }

    public function removeApartment(Housing $apartment): self
    {
        if ($this->apartments->removeElement($apartment)) {
            // set the owning side to null (unless already changed)
            if ($apartment->getElement() === $this) {
                $apartment->setElement(null);
            }
        }

        return $this;
    }
}
