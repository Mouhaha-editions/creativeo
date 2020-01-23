<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PriceRepository")
 */
class Price
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supply", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supply;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Inventory", mappedBy="price")
     */
    private $inventories;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $unitPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $autoUpdateEnabled;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $taxToApply;

    public function __construct()
    {
        $this->inventories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupply(): ?Supply
    {
        return $this->supply;
    }

    public function setSupply(?Supply $supply): self
    {
        $this->supply = $supply;

        return $this;
    }

    /**
     * @return Collection|Inventory[]
     */
    public function getInventories(): Collection
    {
        return $this->inventories;
    }

    public function addInventory(Inventory $inventory): self
    {
        if (!$this->inventories->contains($inventory)) {
            $this->inventories[] = $inventory;
            $inventory->setPrice($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): self
    {
        if ($this->inventories->contains($inventory)) {
            $this->inventories->removeElement($inventory);
            // set the owning side to null (unless already changed)
            if ($inventory->getPrice() === $this) {
                $inventory->setPrice(null);
            }
        }

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getAutoUpdateEnabled(): ?bool
    {
        return $this->autoUpdateEnabled;
    }

    public function setAutoUpdateEnabled(bool $autoUpdateEnabled): self
    {
        $this->autoUpdateEnabled = $autoUpdateEnabled;

        return $this;
    }

    public function getTaxToApply(): ?string
    {
        return $this->taxToApply;
    }

    public function setTaxToApply(string $taxToApply): self
    {
        $this->taxToApply = $taxToApply;

        return $this;
    }
}
