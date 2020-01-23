<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InventoryRepository")
 */
class Inventory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

//    /**
//     * @ORM\ManyToOne(targetEntity="App\Entity\Supply", inversedBy="inventories")
//     */
//    private $supply;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="inventories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $productLabel;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $amountHT;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $amountTTC;
//    /**
//     * @ORM\ManyToOne(targetEntity="App\Entity\Price", inversedBy="inventories")
//     */
//    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getSupply(): ?Supply
//    {
//        return $this->supply;
//    }
//
//    public function setSupply(?Supply $supply): self
//    {
//        $this->supply = $supply;
//
//        return $this;
//    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

//    public function getPrice(): ?Price
//    {
//        return $this->price;
//    }
//
//    public function setPrice(?Price $price): self
//    {
//        $this->price = $price;
//
//        return $this;
//    }
}
