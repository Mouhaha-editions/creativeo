<?php

namespace App\Entity;

use App\Interfaces\IRecipeComponent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeComponentRepository")
 */
class RecipeComponent implements IRecipeComponent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Recipe", inversedBy="recipeComponents")
     */
    private $recipe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Component", inversedBy="recipeComponents")
     */
    private $component;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Unit", inversedBy="recipeComponents")
     */
    private $unit;
    private $optionLabel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getComponent(): ?Component
    {
        return $this->component;
    }

    public function setComponent(?Component $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getQuantity():?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBaseQuantity()
    {
        return $this->quantity / $this->getUnit()->getParentRatio();
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getOptionLabel()
    {
        return $this->optionLabel;
    }

    public function setOptionLabel(?string $optionLabel)
    {
        $this->optionLabel = $optionLabel;
    }
}
