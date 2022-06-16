<?php

namespace App\Entity;

use App\Repository\ServicesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServicesRepository::class)
 */
class Services
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
     * @ORM\ManyToMany(targetEntity=Renter::class, mappedBy="services")
     */
    private $renters;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $state;

    /**
     * @ORM\ManyToMany(targetEntity=Renter::class, mappedBy="completedServices")
     */
    private $completedRenters;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $selector;

    /**
     * @ORM\ManyToMany(targetEntity=Invoice::class, mappedBy="services")
     */
    private $invoices;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\OneToMany(targetEntity=InvoiceContain::class, mappedBy="service", orphanRemoval=true)
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

    public function __construct()
    {
        $this->renters = new ArrayCollection();
        $this->completedRenters = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->invoiceContains = new ArrayCollection();
    }

    public function getSelector(): ?string
    {
        return $this->name .' - '. $this->price . ' €';
    }

    public function __toString(): string
    {
        return $this->name .' ' . $this->price;
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

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
            $renter->addService($this);
        }

        return $this;
    }

    public function removeRenter(Renter $renter): self
    {
        if ($this->renters->removeElement($renter)) {
            $renter->removeService($this);
        }

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

    /**
     * @return Collection|Renter[]
     */
    public function getCompletedRenters(): Collection
    {
        return $this->completedRenters;
    }

    public function addCompletedRenter(Renter $completedRenter): self
    {
        if (!$this->completedRenters->contains($completedRenter)) {
            $this->completedRenters[] = $completedRenter;
            $completedRenter->addCompletedService($this);
        }

        return $this;
    }

    public function removeCompletedRenter(Renter $completedRenter): self
    {
        if ($this->completedRenters->removeElement($completedRenter)) {
            $completedRenter->removeCompletedService($this);
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
            $invoice->addService($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            $invoice->removeService($this);
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
            $invoiceContain->setService($this);
        }

        return $this;
    }

    public function removeInvoiceContain(InvoiceContain $invoiceContain): self
    {
        if ($this->invoiceContains->removeElement($invoiceContain)) {
            // set the owning side to null (unless already changed)
            if ($invoiceContain->getService() === $this) {
                $invoiceContain->setService(null);
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
}
