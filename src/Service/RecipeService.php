<?php


namespace App\Service;


use App\Entity\Inventory;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface|null
     */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, InventoryService $inventoryService, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->inventoryService = $inventoryService;
        $this->tokenStorage = $tokenStorage->getToken();
    }

    public function estimatedCost(Recipe $recipe)
    {
        /** @var User $user */
        $user= $this->entityManager->getRepository(User::class)->find($this->tokenStorage->getUser());
        $sum = 0;
        foreach($recipe->getRecipeComponents() AS $comp){
            $sum += $this->inventoryService->getCostForRecipeComponent($comp);
        }
        return $sum + $user->getHourCost() * $recipe->getEstimatedHours();
    }

    public function SellPriceOptimized(Recipe $recipe)
    {
        $sum = $this->estimatedCost($recipe);
        return $sum / (1-($recipe->getMarge()/100));
    }

    public function canDoIt(Recipe $recipe)
    {
        foreach ($recipe->getRecipeComponents() AS $compo) {
            if( ! $this->inventoryService->hasQuantityForRecipeComponent($compo)){
                return false;
            }
        }
        return true;
    }
}