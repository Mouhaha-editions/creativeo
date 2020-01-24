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
     * @ORM\ManyToOne(targetEntity="Component", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $components;


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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComponent(): ?Component
    {
        return $this->components;
    }

    public function setComponent(?Component $component): self
    {
        $this->components = $component;

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
