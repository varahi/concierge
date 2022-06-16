<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $number;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity=Renter::class, inversedBy="invoices", cascade={"persist"})
     */
    private $renter;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invoices")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity=InvoiceContain::class, mappedBy="invoice", cascade={"persist", "remove"})
     */
    private $contain;

    /**
     * @ORM\ManyToOne(targetEntity=Housing::class, inversedBy="invoices")
     */
    private $apartment;

    /**
     * @ORM\ManyToMany(targetEntity=Services::class, inversedBy="invoices")
     */
    private $services;

    /**
     * @ORM\ManyToMany(targetEntity=Prestation::class, inversedBy="invoices")
     */
    private $prestations;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRenter;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isOwner;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="invoice", cascade={"persist"}, orphanRemoval=true)
     */
    private $task;

    /**
     * @ORM\ManyToMany(targetEntity=Packs::class, inversedBy="invoices")
     */
    private $packs;

    /**
     * @ORM\ManyToMany(targetEntity=Materials::class, inversedBy="invoices")
     */
    private $materials;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $services_qty;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $prestations_qty;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packs_qty;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $materials_qty;

    public function __toString(): string
    {
        return 'Id' . $this->id .' - '. $this->number;
    }

    public function __construct()
    {
        $this->contain = new ArrayCollection();
        $this->created = new \DateTime();
        $this->services = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->task = new ArrayCollection();
        $this->packs = new ArrayCollection();
        $this->materials = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|InvoiceContain[]
     */
    public function getContain(): Collection
    {
        return $this->contain;
    }

    public function addContain(InvoiceContain $contain): self
    {
        if (!$this->contain->contains($contain)) {
            $this->contain[] = $contain;
            $contain->setInvoice($this);
        }

        return $this;
    }

    public function removeContain(InvoiceContain $contain): self
    {
        if ($this->contain->removeElement($contain)) {
            // set the owning side to null (unless already changed)
            if ($contain->getInvoice() === $this) {
                $contain->setInvoice(null);
            }
        }

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

    public function getIsRenter(): ?bool
    {
        return $this->isRenter;
    }

    public function setIsRenter(?bool $isRenter): self
    {
        $this->isRenter = $isRenter;

        return $this;
    }

    public function getIsOwner(): ?bool
    {
        return $this->isOwner;
    }

    public function setIsOwner(?bool $isOwner): self
    {
        $this->isOwner = $isOwner;

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
            $task->setInvoice($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->task->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getInvoice() === $this) {
                $task->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Packs[]
     */
    public function getPacks(): Collection
    {
        return $this->packs;
    }

    public function addPack(Packs $pack): self
    {
        if (!$this->packs->contains($pack)) {
            $this->packs[] = $pack;
        }

        return $this;
    }

    public function removePack(Packs $pack): self
    {
        $this->packs->removeElement($pack);

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
        }

        return $this;
    }

    public function removeMaterial(Materials $material): self
    {
        $this->materials->removeElement($material);

        return $this;
    }

    public function getServicesQty(): ?int
    {
        return $this->services_qty;
    }

    public function setServicesQty(?int $services_qty): self
    {
        $this->services_qty = $services_qty;

        return $this;
    }

    public function getPrestationsQty(): ?int
    {
        return $this->prestations_qty;
    }

    public function setPrestationsQty(?int $prestations_qty): self
    {
        $this->prestations_qty = $prestations_qty;

        return $this;
    }

    public function getPacksQty(): ?int
    {
        return $this->packs_qty;
    }

    public function setPacksQty(?int $packs_qty): self
    {
        $this->packs_qty = $packs_qty;

        return $this;
    }

    public function getMaterialsQty(): ?int
    {
        return $this->materials_qty;
    }

    public function setMaterialsQty(?int $materials_qty): self
    {
        $this->materials_qty = $materials_qty;

        return $this;
    }
}
