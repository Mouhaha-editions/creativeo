<?php

namespace App\Entity;

use App\Interfaces\IRecipeComponent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeFabricationComponentRepository")
 */
class RecipeFabricationComponent implements IRecipeComponent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RecipeFabrication", inversedBy="recipeFabricationComponents")
     */
    private $recipeFabrication;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Component", inversedBy="recipeFabricationComponents")
     */
    private $component;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $quantity = 1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Unit", inversedBy="recipeComponents")
     */
    private $unit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $optionLabel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=true)
     */
    private $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipeFabrication(): ?RecipeFabrication
    {
        return $this->recipeFabrication;
    }

    public function setRecipeFabrication(?RecipeFabrication $recipeFabrication): self
    {
        $this->recipeFabrication = $recipeFabrication;

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

    public function getBaseQuantity()
    {
        return $this->quantity / $this->getUnit()->getParentRatio();
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
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

    public function getOptionLabel(): ?string
    {
        return $this->optionLabel;
    }

    public function setOptionLabel(?string $optionLabel): self
    {
        $this->optionLabel = $optionLabel;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFullName()
    {
        return $this->getComponent()->getLabel().($this->getOptionLabel() != null ? ' - '.$this->getOptionLabel():'');
    }
}
