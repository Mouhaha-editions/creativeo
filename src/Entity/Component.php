<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ComponentRepository")
 */
class Component
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="boolean")
     */
    private $communityEnabled;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Price", mappedBy="components")
     */
    private $prices;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Recipe", mappedBy="components")
     */
    private $recipes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="components")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Inventory", mappedBy="component")
     */
    private $inventories;


    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->inventories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCommunityEnabled(): ?bool
    {
        return $this->communityEnabled;
    }

    public function setCommunityEnabled(bool $communityEnabled): self
    {
        $this->communityEnabled = $communityEnabled;

        return $this;
    }

    /**
     * @return Collection|Price[]
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * @param float $price
     * @return bool
     */
    public function hasPrices(float $price)
    {
        foreach ($this->getPrices() AS $p) {
            if ($p->getUnitPrice() == $price) {
                return true;
            }
        }
        return false;
    }

    public function addPrice(Price $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices[] = $price;
            $price->setComponent($this);
        }

        return $this;
    }

    public function removePrice(Price $price): self
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
            // set the owning side to null (unless already changed)
            if ($price->getComponent() === $this) {
                $price->setComponent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes[] = $recipe;
            $recipe->addComponent($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->contains($recipe)) {
            $this->recipes->removeElement($recipe);
            $recipe->removeComponent($this);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

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
            $inventory->setComponent($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): self
    {
        if ($this->inventories->contains($inventory)) {
            $this->inventories->removeElement($inventory);
            // set the owning side to null (unless already changed)
            if ($inventory->getComponent() === $this) {
                $inventory->setComponent(null);
            }
        }

        return $this;
    }
}
