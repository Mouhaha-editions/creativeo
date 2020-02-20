<?php


namespace App\Service;


use App\Entity\Recipe;
use App\Entity\Taxe;
use App\Entity\User;
use App\Interfaces\IRecipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecipeService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var InventoryService
     */
    private $inventoryService;
    /**
     * @var TokenInterface|null
     */
    private $tokenStorage;
    private $priceWithMarge;

    public function __construct(EntityManagerInterface $entityManager, InventoryService $inventoryService, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->inventoryService = $inventoryService;
        $this->tokenStorage = $tokenStorage->getToken();
    }

    public function marge(IRecipe $recipe)
    {
        $startPrice = $this->SellPriceOptimized($recipe);
        $price = $startPrice - $this->estimatedCost($recipe);
        foreach ($recipe->getTaxes() AS $taxe) {
            if ($taxe->getType() == Taxe::TYPE_PERCENTAGE && $taxe->getEnabled() == true) {
                $price -= $startPrice * $taxe->getValue() / 100;
            } else {
                $price -= $taxe->getValue();
            }
        }
        return $price;
    }
    public function revenus(IRecipe $recipe)
    {
        $startPrice = $this->SellPriceOptimized($recipe);
        $price = $startPrice - $this->estimatedCost($recipe);
        foreach ($recipe->getTaxes() AS $taxe) {
            if ($taxe->getType() == Taxe::TYPE_PERCENTAGE && $taxe->getEnabled() == true) {
                $price -= $startPrice * $taxe->getValue() / 100;
            } else {
                $price -= $taxe->getValue();
            }
        }
        return $price;
    }

    public function priceWithMarge(IRecipe $recipe)
    {
        $sum = $this->estimatedCost($recipe);
        $this->priceWithMarge = $sum * (1 + ($recipe->getMarge() / 100));
        return $this->priceWithMarge;
    }

    public function estimatedCost(IRecipe $recipe)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($this->tokenStorage->getUser());
        $sum = 0;
        foreach ($recipe->getTheRecipeComponents() AS $comp ) {
            $sum += $this->inventoryService->getCostForRecipeComponent($comp);
        }
        return $sum + (($user->getCoutHoraire() + $user->getChargeByHour())* $recipe->getHours()) ;
    }

    public function SellPriceOptimized(IRecipe $recipe)
    {
        $base = $this->priceWithMarge($recipe);
        $taxes_prct = $this->allPercentageTaxes($recipe);
        $taxes_amount = $this->allAmountTaxes($recipe);
        return ($base / $taxes_prct) + $taxes_amount;
    }

    public function allPercentageTaxes(IRecipe $recipe)
    {
        $sumTaxes = 0;
        foreach ($recipe->getTaxes() AS $taxe) {
            if ($taxe->getType() == Taxe::TYPE_PERCENTAGE && $taxe->getEnabled() == true) {
                $sumTaxes += $taxe->getValue();
            }
        }
        return (1 - $sumTaxes / 100);
    }

    public function allAmountTaxes(IRecipe $recipe)
    {
        $sumTaxes = 0;
        foreach ($recipe->getTaxes() AS $taxe) {
            if ($taxe->getType() == Taxe::TYPE_AMOUNT && $taxe->getEnabled() == true) {
                $sumTaxes += $taxe->getValue();
            }
        }
        return $sumTaxes;
    }

    public function canDoIt(IRecipe $recipe)
    {
        foreach ($recipe->getTheRecipeComponents() AS $compo) {
            if (!$this->inventoryService->hasQuantityForRecipeComponent($compo)) {
                return false;
            }
        }
        return true;
    }


}