<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UnitRepository")
 */
class Unit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Unit", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Unit", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8)
     */
    private $parentRatio;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Inventory", mappedBy="unit")
     */
    private $inventories;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecipeComponent", mappedBy="unit")
     */
    private $recipeComponents;

    public function __toString()
    {
        return $this->getLibelle();
    }
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->inventories = new ArrayCollection();
        $this->recipeComponents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getParentRatio(): ?string
    {
        return $this->parentRatio;
    }

    public function setParentRatio(string $parentRatio): self
    {
        $this->parentRatio = $parentRatio;

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
            $inventory->setUnit($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): self
    {
        if ($this->inventories->contains($inventory)) {
            $this->inventories->removeElement($inventory);
            // set the owning side to null (unless already changed)
            if ($inventory->getUnit() === $this) {
                $inventory->setUnit(null);
            }
        }

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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
            $recipeComponent->setUnit($this);
        }

        return $this;
    }

    public function removeRecipeComponent(RecipeComponent $recipeComponent): self
    {
        if ($this->recipeComponents->contains($recipeComponent)) {
            $this->recipeComponents->removeElement($recipeComponent);
            // set the owning side to null (unless already changed)
            if ($recipeComponent->getUnit() === $this) {
                $recipeComponent->setUnit(null);
            }
        }

        return $this;
    }
}
