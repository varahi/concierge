<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 */
class Room
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Housing::class, inversedBy="rooms")
     */
    private $apartment;

    /**
     * @ORM\OneToMany(targetEntity=Objet::class, mappedBy="room", cascade={"persist", "remove"})
     */
    private $objets;

    public function __construct()
    {
        $this->objets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name .' '. $this->apartment;
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

    public function getApartment(): ?Housing
    {
        return $this->apartment;
    }

    public function setApartment(?Housing $apartment): self
    {
        $this->apartment = $apartment;

        return $this;
    }

    /**
     * @return Collection|Objet[]
     */
    public function getObjets(): Collection
    {
        return $this->objets;
    }

    public function addObjet(Objet $objet): self
    {
        if (!$this->objets->contains($objet)) {
            $this->objets[] = $objet;
            $objet->setRoom($this);
        }

        return $this;
    }

    public function removeObjet(Objet $objet): self
    {
        if ($this->objets->removeElement($objet)) {
            // set the owning side to null (unless already changed)
            if ($objet->getRoom() === $this) {
                $objet->setRoom(null);
            }
        }

        return $this;
    }
}
