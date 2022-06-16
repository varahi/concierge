<?php

namespace App\Entity;

use App\Repository\InvoiceContainRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvoiceContainRepository::class)
 */
class InvoiceContain
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
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity=Invoice::class, inversedBy="contain")
     */
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity=Services::class, inversedBy="invoiceContains")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity=Prestation::class, inversedBy="invoiceContains")
     */
    private $prestation;

    /**
     * @ORM\ManyToOne(targetEntity=Packs::class, inversedBy="invoiceContains")
     */
    private $pack;

    /**
     * @ORM\ManyToOne(targetEntity=Materials::class, inversedBy="invoiceContains")
     */
    private $material;

    public function __toString(): string
    {
        return 'Id' . $this->id .' - '. $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getService(): ?Services
    {
        return $this->service;
    }

    public function setService(?Services $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getPrestation(): ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestation $prestation): self
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getPack(): ?Packs
    {
        return $this->pack;
    }

    public function setPack(?Packs $pack): self
    {
        $this->pack = $pack;

        return $this;
    }

    public function getMaterial(): ?Materials
    {
        return $this->material;
    }

    public function setMaterial(?Materials $material): self
    {
        $this->material = $material;

        return $this;
    }
}
