<?php

namespace App\Entity;

use App\Interfaces\IRecipe;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeFabricationRepository")
 */
class RecipeFabrication implements IRecipe
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Recipe", inversedBy="recipeFabrications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipe;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=5)
     */
    private $marge;

    /**
     * @ORM\Column(type="decimal",precision=10,scale=4)
     */
    private $quantity;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Taxe", inversedBy="recipeFabrications")
     */
    private $taxes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecipeFabricationComponent", mappedBy="recipeFabrication",cascade={"persist"})
     */
    private $recipeFabricationComponents;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $hours=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ended = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $finalised = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Unit", inversedBy="recipeFabrications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $unit;

    public function __construct()
    {
        $this->taxes = new ArrayCollection();
        $this->recipeFabricationComponents = new ArrayCollection();
    }

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

    public function getMarge(): ?string
    {
        return $this->marge;
    }

    public function setMarge(string $marge): self
    {
        $this->marge = $marge;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Collection|Taxe[]
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function addTax(Taxe $tax): self
    {
        if (!$this->taxes->contains($tax)) {
            $this->taxes[] = $tax;
        }

        return $this;
    }

    public function removeTax(Taxe $tax): self
    {
        if ($this->taxes->contains($tax)) {
            $this->taxes->removeElement($tax);
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

    public function addRecipeFabricationComponents(RecipeFabricationComponent $recipeFabricationComponent): self
    {
        if (!$this->recipeFabricationComponents->contains($recipeFabricationComponent)) {
            $this->recipeFabricationComponents[] = $recipeFabricationComponent;
        }

        return $this;
    }

    public function removeRecipeFabricationComponent(RecipeFabricationComponent $recipeFabricationComponent): self
    {
        if ($this->recipeFabricationComponents->contains($recipeFabricationComponent)) {
            $this->recipeFabricationComponents->removeElement($recipeFabricationComponent);
        }

        return $this;
    }
    public function getHours(): ?string
    {
        return $this->hours;
    }

    public function setHours(string $hours): self
    {
        $this->hours = $hours;

        return $this;
    }

    public function getEnded(): bool
    {
        return $this->ended;
    }

    public function setEnded(bool $ended = null): self
    {
        $ended = $ended === null ? false: $ended;
        $this->ended = $ended;

        return $this;
    }

    public function getCalculatedHours()
    {
        $diff = (new \DateTime())->diff($this->getCreatedAt());
        $hours = round($diff->s / 3600 + $diff->i / 60 + $diff->h + $diff->days * 24, 2);
        return $hours;
    }
    /**
     * @return Collection|RecipeComponent[]
     */
    public function getTheRecipeComponents(): Collection
    {
        return $this->getRecipeFabricationComponents();
    }

    public function getFinalised(): ?bool
    {
        return $this->finalised;
    }

    public function setFinalised(?bool $finalised): self
    {
        $this->finalised = $finalised;

        return $this;
    }

    public function getAmount()
    {
        $amount = 0;
        foreach($this->getRecipeFabricationComponents() AS $component){
            $amount += $component->getAmount();
        }
        return $amount;
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
}
