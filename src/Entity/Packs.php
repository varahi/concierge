<?php

namespace App\Entity;

use App\Repository\PacksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacksRepository::class)
 */
class Packs
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
     * @ORM\OneToMany(targetEntity=Housing::class, mappedBy="packs")
     */
    private $housings;

    /**
     * @ORM\ManyToMany(targetEntity=Prestation::class, inversedBy="packs")
     */
    private $contain;

    /**
     * @ORM\OneToMany(targetEntity=Params::class, mappedBy="packs")
     */
    private $params;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $total;

    /**
     * @ORM\ManyToMany(targetEntity=Invoice::class, mappedBy="packs")
     */
    private $invoices;

    /**
     * @ORM\OneToMany(targetEntity=InvoiceContain::class, mappedBy="pack", orphanRemoval=true)
     */
    private $invoiceContains;

    public function __construct()
    {
        $this->housings = new ArrayCollection();
        $this->contain = new ArrayCollection();
        $this->params = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->invoiceContains = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name .' ' . $this->total;
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
     * @return Collection|Housing[]
     */
    public function getHousings(): Collection
    {
        return $this->housings;
    }

    public function addHousing(Housing $housing): self
    {
        if (!$this->housings->contains($housing)) {
            $this->housings[] = $housing;
            $housing->setPacks($this);
        }

        return $this;
    }

    public function removeHousing(Housing $housing): self
    {
        if ($this->housings->removeElement($housing)) {
            // set the owning side to null (unless already changed)
            if ($housing->getPacks() === $this) {
                $housing->setPacks(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Prestation[]
     */
    public function getContain(): Collection
    {
        return $this->contain;
    }

    public function addContain(Prestation $contain): self
    {
        if (!$this->contain->contains($contain)) {
            $this->contain[] = $contain;
        }

        return $this;
    }

    public function removeContain(Prestation $contain): self
    {
        $this->contain->removeElement($contain);

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
            $param->setPacks($this);
        }

        return $this;
    }

    public function removeParam(Params $param): self
    {
        if ($this->params->removeElement($param)) {
            // set the owning side to null (unless already changed)
            if ($param->getPacks() === $this) {
                $param->setPacks(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;
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
            $invoice->addPack($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            $invoice->removePack($this);
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
            $invoiceContain->setPack($this);
        }

        return $this;
    }

    public function removeInvoiceContain(InvoiceContain $invoiceContain): self
    {
        if ($this->invoiceContains->removeElement($invoiceContain)) {
            // set the owning side to null (unless already changed)
            if ($invoiceContain->getPack() === $this) {
                $invoiceContain->setPack(null);
            }
        }

        return $this;
    }
}
