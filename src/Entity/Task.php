<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
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
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $selector;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $notification;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=Housing::class, inversedBy="task")
     */
    private $housing;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="task")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Calendar::class, inversedBy="task")
     */
    private $calendar;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isArchived;

    /**
     * @ORM\ManyToOne(targetEntity=Renter::class, inversedBy="tasks")
     */
    private $renter;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isEntry;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSingle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity=Invoice::class, inversedBy="task", cascade={"persist", "remove"})
     */
    private $invoice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isEvent;

    /**
     * @ORM\ManyToMany(targetEntity=Prestation::class, inversedBy="tasks")
     */
    private $prestations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $owner_note;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $renter_note;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPrestation;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->prestations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'Task # ' . $this->id .' - '. $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getSelector(): ?string
    {
        return 'Task #' . $this->id .' - '. $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getEndHour(): ?string
    {
        return $this->endHour;
    }

    public function setEndHour(?string $endHour): self
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getNotification(): ?int
    {
        return $this->notification;
    }

    public function setNotification(?string $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getHousing(): ?Housing
    {
        return $this->housing;
    }

    public function setHousing(?Housing $housing): self
    {
        $this->housing = $housing;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addTask($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeTask($this);
        }

        return $this;
    }

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(?Calendar $calendar): self
    {
        $this->calendar = $calendar;

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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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

    public function getRenter(): ?Renter
    {
        return $this->renter;
    }

    public function setRenter(?Renter $renter): self
    {
        $this->renter = $renter;

        return $this;
    }

    public function getIsEntry(): ?bool
    {
        return $this->isEntry;
    }

    public function setIsEntry(?int $isEntry): self
    {
        $this->isEntry = $isEntry;

        return $this;
    }

    public function getIsSingle(): ?bool
    {
        return $this->isSingle;
    }

    public function setIsSingle(?bool $isSingle): self
    {
        $this->isSingle = $isSingle;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getIsEvent(): ?bool
    {
        return $this->isEvent;
    }

    public function setIsEvent(?bool $isEvent): self
    {
        $this->isEvent = $isEvent;

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

    public function getOwnerNote(): ?string
    {
        return $this->owner_note;
    }

    public function setOwnerNote(?string $owner_note): self
    {
        $this->owner_note = $owner_note;

        return $this;
    }

    public function getRenterNote(): ?string
    {
        return $this->renter_note;
    }

    public function setRenterNote(?string $renter_note): self
    {
        $this->renter_note = $renter_note;

        return $this;
    }

    public function getIsPrestation(): ?bool
    {
        return $this->isPrestation;
    }

    public function setIsPrestation(?bool $isPrestation): self
    {
        $this->isPrestation = $isPrestation;

        return $this;
    }
}
