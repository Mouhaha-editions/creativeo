<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaxeRepository")
 */
class Taxe
{
    public const TYPE_PERCENTAGE = 10;
    public const TYPE_AMOUNT = 20;

    public const Types = [
        "entity.taxe.label.type_percentage" => self::TYPE_PERCENTAGE,
        "entity.taxe.label.type_amount" => self::TYPE_AMOUNT,
    ];

    const ReverseTypes = [
        self::TYPE_AMOUNT => "entity.taxe.label.type_amount",
        self::TYPE_PERCENTAGE => "entity.taxe.label.type_percentage",
    ];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="taxes")
     */
    private $user;

    /**
     * @ORM\Column(type="decimal", precision=50, scale=5)
     */
    private $value;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Recipe", mappedBy="taxes")
     */
    private $recipes;

    /**
     * @ORM\Column(type="integer")
     */
    private $Type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDefault;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnabledForCommunity;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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
            $recipe->addTax($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->contains($recipe)) {
            $this->recipes->removeElement($recipe);
            $recipe->removeTax($this);
        }

        return $this;
    }

    public function getTypeStr(): ?string
    {
        return self::ReverseTypes[$this->getType()];
    }

    public function getType(): ?int
    {
        return $this->Type;
    }

    public function setType(int $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getIsEnabledForCommunity(): ?bool
    {
        return $this->isEnabledForCommunity;
    }

    public function setIsEnabledForCommunity(bool $isEnabledForCommunity): self
    {
        $this->isEnabledForCommunity = $isEnabledForCommunity;

        return $this;
    }

}
