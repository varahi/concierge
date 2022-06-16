<?php

namespace App\Entity;

use App\Repository\MaterialsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MaterialsRepository::class)
 */
class Materials
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $dayPrice;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $weekPrice;

    /**
     * @ORM\ManyToOne(targetEntity=Renter::class, inversedBy="materials")
     */
    private $renter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $selector;

    /**
     * @ORM\ManyToMany(targetEntity=Invoice::class, mappedBy="materials")
     */
    private $invoices;

    /**
     * @ORM\OneToMany(targetEntity=InvoiceContain::class, mappedBy="material", orphanRemoval=true)
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

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(?string $stock): self
    {
        $this->stock = $stock;

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

    public function getDayPrice(): ?string
    {
        return $this->dayPrice;
    }

    public function setDayPrice(?string $dayPrice): self
    {
        $this->dayPrice = $dayPrice;

        return $this;
    }

    public function getWeekPrice(): ?string
    {
        return $this->weekPrice;
    }

    public function setWeekPrice(string $weekPrice): self
    {
        $this->weekPrice = $weekPrice;

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
            $invoice->addMaterial($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            $invoice->removeMaterial($this);
        }

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
            $invoiceContain->setMaterial($this);
        }

        return $this;
    }

    public function removeInvoiceContain(InvoiceContain $invoiceContain): self
    {
        if ($this->invoiceContains->removeElement($invoiceContain)) {
            // set the owning side to null (unless already changed)
            if ($invoiceContain->getMaterial() === $this) {
                $invoiceContain->setMaterial(null);
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
