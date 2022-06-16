<?php

namespace App\Entity;

use App\Repository\CalendarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass=CalendarRepository::class)
 */
class Calendar
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="calendar")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="calendar", cascade={"persist", "remove"})
     */
    private $task;

    /**
     * @ORM\Column(type="date")
     * @OrderBy({"name" = "ASC"})
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
     * @ORM\ManyToOne(targetEntity=Renter::class, inversedBy="calendar")
     */
    private $renter;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isArchived;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSingle;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="calendar", cascade={"persist", "remove"})
     */
    private $reservations;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $created;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->task = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!empty($this->startDate) && !empty($this->endDate)) {
            //return $this->startDate->format('Y-m-d') .' '. $this->startHour->format('H:i:s');
            return 'Id ' . $this->id .' - '. $this->startDate->format('Y-m-d') .' - '. $this->endDate->format('Y-m-d');
        } else {
            return $this->startDate->format('Y-m-d');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $task->setCalendar($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->task->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getCalendar() === $this) {
                $task->setCalendar(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
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

    public function getRenter(): ?Renter
    {
        return $this->renter;
    }

    public function setRenter(?Renter $renter): self
    {
        $this->renter = $renter;

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

    public function getIsSingle(): ?bool
    {
        return $this->isSingle;
    }

    public function setIsSingle(?bool $isSingle): self
    {
        $this->isSingle = $isSingle;

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
            $reservation->setCalendar($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCalendar() === $this) {
                $reservation->setCalendar(null);
            }
        }

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
