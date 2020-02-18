<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeRepository")
 */
class Recipe
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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $estimatedHours;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="receipts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecipeComponent", mappedBy="recipe",cascade={"persist","remove"})
     */
    private $recipeComponents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Creation", mappedBy="recipe", orphanRemoval=true)
     */
    private $creations;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=5)
     */
    private $marge;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Taxe", inversedBy="recipes")
     */
    private $taxes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compteur", inversedBy="recipes")
     */
    private $compteur;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $community;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecipeFabrication", mappedBy="recipe", orphanRemoval=true)
     */
    private $recipeFabrications;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photoPath;

    public function __construct()
    {
        $this->recipeComponents = new ArrayCollection();
        $this->creations = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->recipeFabrications = new ArrayCollection();
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

    public function getEstimatedHours(): ?string
    {
        return $this->estimatedHours;
    }

    public function setEstimatedHours(string $estimatedHours): self
    {
        $this->estimatedHours = $estimatedHours;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

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
            $recipeComponent->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeComponent(RecipeComponent $recipeComponent): self
    {
        if ($this->recipeComponents->contains($recipeComponent)) {
            $this->recipeComponents->removeElement($recipeComponent);
            // set the owning side to null (unless already changed)
            if ($recipeComponent->getRecipe() === $this) {
                $recipeComponent->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Creation[]
     */
    public function getCreations(): Collection
    {
        return $this->creations;
    }

    public function addCreation(Creation $creation): self
    {
        if (!$this->creations->contains($creation)) {
            $this->creations[] = $creation;
            $creation->setRecipe($this);
        }

        return $this;
    }

    public function removeCreation(Creation $creation): self
    {
        if ($this->creations->contains($creation)) {
            $this->creations->removeElement($creation);
            // set the owning side to null (unless already changed)
            if ($creation->getRecipe() === $this) {
                $creation->setRecipe(null);
            }
        }

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

    /**
     * @return Collection|taxe[]
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function addTax(taxe $tax): self
    {
        if (!$this->taxes->contains($tax)) {
            $this->taxes[] = $tax;
        }

        return $this;
    }

    public function removeTax(taxe $tax): self
    {
        if ($this->taxes->contains($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

    public function getCompteur(): ?Compteur
    {
        return $this->compteur;
    }

    public function setCompteur(?Compteur $compteur): self
    {
        $this->compteur = $compteur;

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

    public function getCommunity(): ?bool
    {
        return $this->community;
    }

    public function setCommunity(bool $community): self
    {
        $this->community = $community;

        return $this;
    }

    /**
     * @return Collection|RecipeFabrication[]
     */
    public function getRecipeFabrications(): Collection
    {
        return $this->recipeFabrications;
    }
    /**
     * @return RecipeFabrication
     */
    public function getNotEndedRecipeFabrication(): ?RecipeFabrication
    {
        foreach($this->getRecipeFabrications() AS $fabrication){
            if(!$fabrication->getEnded()){
                return $fabrication;
            }
        }
        return null;
    }

    public function addRecipeFabrication(RecipeFabrication $recipeFabrication): self
    {
        if (!$this->recipeFabrications->contains($recipeFabrication)) {
            $this->recipeFabrications[] = $recipeFabrication;
            $recipeFabrication->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeFabrication(RecipeFabrication $recipeFabrication): self
    {
        if ($this->recipeFabrications->contains($recipeFabrication)) {
            $this->recipeFabrications->removeElement($recipeFabrication);
            // set the owning side to null (unless already changed)
            if ($recipeFabrication->getRecipe() === $this) {
                $recipeFabrication->setRecipe(null);
            }
        }

        return $this;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): self
    {
        $this->photoPath = $photoPath;

        return $this;
    }
}
