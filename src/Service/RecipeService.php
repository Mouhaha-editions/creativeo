<?php


namespace App\Service;


use App\Entity\Recipe;
use App\Entity\Taxe;
use App\Entity\User;
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

    public function marge(Recipe $recipe)
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
    public function revenus(Recipe $recipe)
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

    public function priceWithMarge(Recipe $recipe)
    {
        $sum = $this->estimatedCost($recipe);
        $this->priceWithMarge = $sum * (1 + ($recipe->getMarge() / 100));
        return $this->priceWithMarge;
    }

    public function estimatedCost(Recipe $recipe)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($this->tokenStorage->getUser());
        $sum = 0;
        foreach ($recipe->getRecipeComponents() AS $comp ) {
            $sum += $this->inventoryService->getCostForRecipeComponent($comp);
        }
        return $sum + (($user->getCoutHoraire() + $user->getChargeByHour())* $recipe->getEstimatedHours()) ;
    }

    public function SellPriceOptimized(Recipe $recipe)
    {
        $base = $this->priceWithMarge($recipe);
        $taxes_prct = $this->allPercentageTaxes($recipe);
        $taxes_amount = $this->allAmountTaxes($recipe);
        return ($base / $taxes_prct) + $taxes_amount;
    }

    public function allPercentageTaxes(Recipe $recipe)
    {
        $sumTaxes = 0;
        foreach ($recipe->getTaxes() AS $taxe) {
            if ($taxe->getType() == Taxe::TYPE_PERCENTAGE && $taxe->getEnabled() == true) {
                $sumTaxes += $taxe->getValue();
            }
        }
        return (1 - $sumTaxes / 100);
    }

    private function allAmountTaxes(Recipe $recipe)
    {
        $sumTaxes = 0;
        foreach ($recipe->getTaxes() AS $taxe) {
            if ($taxe->getType() == Taxe::TYPE_AMOUNT && $taxe->getEnabled() == true) {
                $sumTaxes += $taxe->getValue();
            }
        }
        return $sumTaxes;
    }

    public function canDoIt(Recipe $recipe)
    {
        foreach ($recipe->getRecipeComponents() AS $compo) {
            if (!$this->inventoryService->hasQuantityForRecipeComponent($compo)) {
                return false;
            }
        }
        return true;
    }


}