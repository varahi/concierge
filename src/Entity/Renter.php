<?php

namespace App\Entity;

use App\Repository\RenterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RenterRepository::class)
 */
class Renter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numAdults;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numChildren;

    /**
     * @ORM\ManyToMany(targetEntity=Services::class, inversedBy="renters")
     */
    private $services;

    /**
     * @ORM\ManyToMany(targetEntity=Services::class, inversedBy="completedRenters")
     * @ORM\JoinTable(name="renter_cservices",
     *  joinColumns={@ORM\JoinColumn(name="renter_id", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="services_id", referencedColumnName="id")}
     *  )
     */
    private $completedServices;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isArchived;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $selector;

    /**
     * @ORM\ManyToMany(targetEntity=Prestation::class, inversedBy="renters")
     */
    private $prestations;

    /**
     * @ORM\OneToMany(targetEntity=Calendar::class, mappedBy="renter", cascade={"persist", "remove"})
     */
    private $calendar;

    /**
     * @ORM\OneToMany(targetEntity=Materials::class, mappedBy="renter")
     */
    private $materials;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="renter")
     */
    private $tasks;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $startHour;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $endHour;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="renter", cascade={"persist", "remove"})
     */
    private $invoices;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numLittleChildren;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $created;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->services = new ArrayCollection();
        $this->completedServices = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->calendar = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'id' . $this->id .' - '. $this->address .' - ' . $this->firstName . ' ' .$this->lastName;
    }

    public function getSelector(): ?string
    {
        return $this->address .' - ' . $this->firstName . ' ' .$this->lastName;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getNumAdults(): ?int
    {
        return $this->numAdults;
    }

    public function setNumAdults(?int $numAdults): self
    {
        $this->numAdults = $numAdults;

        return $this;
    }

    public function getNumChildren(): ?int
    {
        return $this->numChildren;
    }

    public function setNumChildren(?int $numChildren): self
    {
        $this->numChildren = $numChildren;

        return $this;
    }

    /**
     * @return Collection|Services[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Services $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
        }

        return $this;
    }

    public function removeService(Services $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }

    /**
     * @return Collection|Services[]
     */
    public function getCompletedServices(): Collection
    {
        return $this->completedServices;
    }

    public function addCompletedService(Services $completedService): self
    {
        if (!$this->completedServices->contains($completedService)) {
            $this->completedServices[] = $completedService;
        }

        return $this;
    }

    public function removeCompletedService(Services $completedService): self
    {
        $this->completedServices->removeElement($completedService);

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(?bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * @return Collection|Prestation[]
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations[] = $prestation;
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        $this->prestations->removeElement($prestation);

        return $this;
    }

    /**
     * @return Collection|Calendar[]
     */
    public function getCalendar(): Collection
    {
        return $this->calendar;
    }

    public function addCalendar(Calendar $calendar): self
    {
        if (!$this->calendar->contains($calendar)) {
            $this->calendar[] = $calendar;
            $calendar->setRenter($this);
        }

        return $this;
    }

    public function removeCalendar(Calendar $calendar): self
    {
        if ($this->calendar->removeElement($calendar)) {
            // set the owning side to null (unless already changed)
            if ($calendar->getRenter() === $this) {
                $calendar->setRenter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Materials[]
     */
    public function getMaterials(): Collection
    {
        return $this->materials;
    }

    public function addMaterial(Materials $material): self
    {
        if (!$this->materials->contains($material)) {
            $this->materials[] = $material;
            $material->setRenter($this);
        }

        return $this;
    }

    public function removeMaterial(Materials $material): self
    {
        if ($this->materials->removeElement($material)) {
            // set the owning side to null (unless already changed)
            if ($material->getRenter() === $this) {
                $material->setRenter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setRenter($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getRenter() === $this) {
                $task->setRenter(null);
            }
        }

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(?\DateTimeInterface $startHour): self
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeInterface
    {
        return $this->endHour;
    }

    public function setEndHour(?\DateTimeInterface $endHour): self
    {
        $this->endHour = $endHour;

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
            $invoice->setRenter($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getRenter() === $this) {
                $invoice->setRenter(null);
            }
        }

        return $this;
    }

    public function getNumLittleChildren(): ?int
    {
        return $this->numLittleChildren;
    }

    public function setNumLittleChildren(?int $numLittleChildren): self
    {
        $this->numLittleChildren = $numLittleChildren;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }
}
