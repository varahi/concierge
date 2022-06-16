<?php

namespace App\Entity;

use App\Repository\HousingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HousingRepository::class)
 */
class Housing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="housing")
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity=Packs::class, inversedBy="housings")
     */
    private $packs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * Owner
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="apartments")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Elements::class, inversedBy="apartments", cascade={"persist", "remove"})
     */
    private $element;

    /**
     * @ORM\OneToMany(targetEntity=Room::class, mappedBy="apartment")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="apartment")
     */
    private $invoices;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="employerAppts")
     */
    private $employer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $residence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apartment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logement;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isHidden;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numRooms;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numBeds;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="housing")
     */
    private $reservations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $selector;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="agencyApartments")
     */
    private $agency;

    public function __toString(): string
    {
        return 'Appt ' . $this->id .' - '. $this->address;
        //return $this->name;
    }

    public function __construct()
    {
        $this->task = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->agency = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelector(): ?string
    {
        return 'Appt ' . $this->id .' - '. $this->address;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
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

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTask(): Collection
    {
        return $this->task;
    }

    public function addTask(Task $task): self
    {
        if (!$this->task->contains($task)) {
            $this->task[] = $task;
            $task->setHousing($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->task->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getHousing() === $this) {
                $task->setHousing(null);
            }
        }

        return $this;
    }

    public function getPacks(): ?Packs
    {
        return $this->packs;
    }

    public function setPacks(?Packs $packs): self
    {
        $this->packs = $packs;

        return $this;
    }


    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getElement(): ?Elements
    {
        return $this->element;
    }

    public function setElement(?Elements $element): self
    {
        $this->element = $element;

        return $this;
    }

    /**
     * @return Collection|Room[]
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms[] = $room;
            $room->setApartment($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getApartment() === $this) {
                $room->setApartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setApartment($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getApartment() === $this) {
                $invoice->setApartment(null);
            }
        }

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

    public function getEmployer(): ?User
    {
        return $this->employer;
    }

    public function setEmployer(?User $employer): self
    {
        $this->employer = $employer;

        return $this;
    }

    public function getResidence(): ?string
    {
        return $this->residence;
    }

    public function setResidence(?string $residence): self
    {
        $this->residence = $residence;

        return $this;
    }

    public function getApartment(): ?string
    {
        return $this->apartment;
    }

    public function setApartment(?string $apartment): self
    {
        $this->apartment = $apartment;

        return $this;
    }

    public function getLogement(): ?string
    {
        return $this->logement;
    }

    public function setLogement(?string $logement): self
    {
        $this->logement = $logement;

        return $this;
    }

    public function getIsHidden(): ?bool
    {
        return $this->isHidden;
    }

    public function setIsHidden(?bool $isHidden): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    public function getNumRooms(): ?int
    {
        return $this->numRooms;
    }

    public function setNumRooms(?int $numRooms): self
    {
        $this->numRooms = $numRooms;

        return $this;
    }

    public function getNumBeds(): ?int
    {
        return $this->numBeds;
    }

    public function setNumBeds(?int $numBeds): self
    {
        $this->numBeds = $numBeds;

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setHousing($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getHousing() === $this) {
                $reservation->setHousing(null);
            }
        }

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAgency(): Collection
    {
        return $this->agency;
    }

    public function addAgency(User $agency): self
    {
        if (!$this->agency->contains($agency)) {
            $this->agency[] = $agency;
        }

        return $this;
    }

    public function removeAgency(User $agency): self
    {
        $this->agency->removeElement($agency);

        return $this;
    }
}
