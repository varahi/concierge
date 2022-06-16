<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrestationRepository::class)
 */
class Prestation
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
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity=Packs::class, mappedBy="contain", orphanRemoval=true)
     */
    private $packs;

    /**
     * @ORM\OneToMany(targetEntity=Params::class, mappedBy="prestation", orphanRemoval=true)
     */
    private $params;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortName;

    /**
     * @ORM\ManyToMany(targetEntity=Renter::class, mappedBy="prestations")
     */
    private $renters;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $selector;

    /**
     * @ORM\ManyToMany(targetEntity=Invoice::class, mappedBy="prestations")
     */
    private $invoices;

    /**
     * @ORM\ManyToMany(targetEntity=Task::class, mappedBy="prestations")
     */
    private $tasks;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\OneToMany(targetEntity=InvoiceContain::class, mappedBy="prestation", orphanRemoval=true)
     */
    private $invoiceContains;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRenter;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isOwner;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isTask;

    public function __toString(): string
    {
        return $this->id .' - '. $this->name .' - '. $this->price;
    }

    public function getSelector(): ?string
    {
        return $this->name .' - '. $this->price . ' €';
    }

    public function __construct()
    {
        $this->packs = new ArrayCollection();
        $this->params = new ArrayCollection();
        $this->renters = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->invoiceContains = new ArrayCollection();
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

    /**
     * @return Collection|Packs[]
     */
    public function getPacks(): Collection
    {
        return $this->packs;
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

    public function addPack(Packs $pack): self
    {
        if (!$this->packs->contains($pack)) {
            $this->packs[] = $pack;
            $pack->addContain($this);
        }

        return $this;
    }

    public function removePack(Packs $pack): self
    {
        if ($this->packs->removeElement($pack)) {
            $pack->removeContain($this);
        }

        return $this;
    }

    /**
     * @return Collection|Params[]
     */
    public function getParams(): Collection
    {
        return $this->params;
    }

    public function addParam(Params $param): self
    {
        if (!$this->params->contains($param)) {
            $this->params[] = $param;
            $param->setPrestation($this);
        }

        return $this;
    }

    public function removeParam(Params $param): self
    {
        if ($this->params->removeElement($param)) {
            // set the owning side to null (unless already changed)
            if ($param->getPrestation() === $this) {
                $param->setPrestation(null);
            }
        }

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return Collection|Renter[]
     */
    public function getRenters(): Collection
    {
        return $this->renters;
    }

    public function addRenter(Renter $renter): self
    {
        if (!$this->renters->contains($renter)) {
            $this->renters[] = $renter;
            $renter->addPrestation($this);
        }

        return $this;
    }

    public function removeRenter(Renter $renter): self
    {
        if ($this->renters->removeElement($renter)) {
            $renter->removePrestation($this);
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
            $invoice->addPrestation($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            $invoice->removePrestation($this);
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
            $task->addPrestation($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            $task->removePrestation($this);
        }

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Collection|InvoiceContain[]
     */
    public function getInvoiceContains(): Collection
    {
        return $this->invoiceContains;
    }

    public function addInvoiceContain(InvoiceContain $invoiceContain): self
    {
        if (!$this->invoiceContains->contains($invoiceContain)) {
            $this->invoiceContains[] = $invoiceContain;
            $invoiceContain->setPrestation($this);
        }

        return $this;
    }

    public function removeInvoiceContain(InvoiceContain $invoiceContain): self
    {
        if ($this->invoiceContains->removeElement($invoiceContain)) {
            // set the owning side to null (unless already changed)
            if ($invoiceContain->getPrestation() === $this) {
                $invoiceContain->setPrestation(null);
            }
        }

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

    public function getIsTask(): ?bool
    {
        return $this->isTask;
    }

    public function setIsTask(?bool $isTask): self
    {
        $this->isTask = $isTask;

        return $this;
    }
}
