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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="components")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Inventory", mappedBy="component")
     */
    private $inventories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecipeComponent", mappedBy="component")
     * @ORM\Column(nullable=true)

     */
    private $recipeComponents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecipeFabricationComponent", mappedBy="component")
     * @ORM\Column(nullable=true)
     */
    private $recipeFabricationComponents;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->inventories = new ArrayCollection();
        $this->recipeComponents = new ArrayCollection();
        $this->recipeFabricationComponents = new ArrayCollection();
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

    /**
     * @return Collection|RecipeComponent[]
     */
    public function getRecipeComponents(): Collection
    {
        return $this->recipeComponents;
    }

    public function addRecipeComponent(RecipeComponent $recipeComponent): self
    {
        if (!$this->recipeComponents->contains($recipeComponent)) {
            $this->recipeComponents[] = $recipeComponent;
            $recipeComponent->setComponent($this);
        }

        return $this;
    }

    public function removeRecipeComponent(RecipeComponent $recipeComponent): self
    {
        if ($this->recipeComponents->contains($recipeComponent)) {
            $this->recipeComponents->removeElement($recipeComponent);
            // set the owning side to null (unless already changed)
            if ($recipeComponent->getComponent() === $this) {
                $recipeComponent->setComponent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RecipeFabricationComponent[]
     */
    public function getRecipeFabricationComponents(): Collection
    {
        return $this->recipeFabricationComponents;
    }

    public function addRecipeFabricationComponent(RecipeFabricationComponent $recipeFabricationComponent): self
    {
        if (!$this->recipeFabricationComponents->contains($recipeFabricationComponent)) {
            $this->recipeFabricationComponents[] = $recipeFabricationComponent;
            $recipeFabricationComponent->setComponent($this);
        }

        return $this;
    }

    public function removeRecipeFabricationComponent(RecipeFabricationComponent $recipeFabricationComponent): self
    {
        if ($this->recipeFabricationComponents->contains($recipeFabricationComponent)) {
            $this->recipeFabricationComponents->removeElement($recipeFabricationComponent);
            // set the owning side to null (unless already changed)
            if ($recipeFabricationComponent->getComponent() === $this) {
                $recipeFabricationComponent->setComponent(null);
            }
        }

        return $this;
    }
}
