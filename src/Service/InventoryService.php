<?php


namespace App\Service;


use App\Entity\Inventory;
use App\Entity\RecipeComponent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InventoryService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage->getToken();
    }

    public function countTotalPrice()
    {
        $result = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->select('SUM(i.quantity*i.price)')
            ->where('i.user = :user')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->groupBy('i.user')
            ->getQuery()->getOneOrNullResult();

        $res = is_array($result) ? array_pop($result) : null;
        return $res == null ? 0 : $res;
    }

    public function countQuantity()
    {
        $result = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->where('i.user = :user')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->groupBy('i.user')
            ->getQuery()->getOneOrNullResult();
        $res = is_array($result) ? array_pop($result) : null;
        return $res == null ? 0 : $res;

    }

    public function hasQuantityForRecipeComponent(RecipeComponent $compo)
    {
        $result = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->leftJoin('i.unit', 'unit')
            ->select('SUM(i.quantity)')
            ->where('i.user = :user')
            ->andWhere('i.component = :component')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->setParameter('component', $compo->getComponent())
            ->groupBy('i.user')
            ->getQuery()->getOneOrNullResult();

        $res = is_array($result) ? array_pop($result) : 0;
        return $res >= $compo->getQuantity();
    }

    public function getCostForRecipeComponent(RecipeComponent $compo)
    {

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($this->tokenStorage->getUser());


        /** @var Inventory[] $inventoires */
        $inventoires = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->leftJoin('i.unit', 'unit')
            ->where('i.user = :user')
            ->andWhere('i.component = :component')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->setParameter('component', $compo->getComponent())
            ->orderBy('i.price', $user->getUseOrderPreference())
            ->getQuery()->getResult();
        $sum = 0;
        $quantityNeeded = $compo->getQuantity();
        foreach ($inventoires AS $inventory) {
            if(!$this->hasQuantityForRecipeComponent($compo)){
                $sum += $inventory->getPrice()* $quantityNeeded;
                break;
            }
            if($inventory->getQuantity()>=$quantityNeeded){
                $sum += $inventory->getPrice()* $quantityNeeded;
                break;
            }else{
                $reste = $quantityNeeded - $inventory->getQuantity();
                $sum += ($quantityNeeded-$reste) *$inventory->getPrice();
                $quantityNeeded = $reste;
            }
        }
        return $sum;
    }

}