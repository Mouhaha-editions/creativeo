<?php


namespace App\Service;


use App\Entity\Component;
use App\Entity\Inventory;
use App\Entity\RecipeComponent;
use App\Entity\User;
use App\Interfaces\IRecipeComponent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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

    public function getCostForRecipeComponent(IRecipeComponent $compo)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($this->tokenStorage->getUser());

        $option = $compo->getOptionLabel();
        /** @var Inventory[] $inventoires */
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i');
        $qb
            ->leftJoin('i.unit', 'unit')
            ->where('i.user = :user')
            ->andWhere('i.component = :component')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->setParameter('component', $compo->getComponent())
            ->orderBy('i.price', $user->getUseOrderPreference());
        if ($compo->getOptionLabel() != null) {
            $qb->andWhere($qb->expr()->eq('i.optionLabel', ':option'))
                ->setParameter('option', $compo->getOptionLabel());
        } else {
            $qb->andWhere($qb->expr()->isNull('i.optionLabel'));
        }

        $inventoires = $qb->getQuery()->getResult();
        $sum = 0;
        // compos quantity = 500 g
        //inventaire = 0.500 kg

        $quantityNeeded = $compo->getBaseQuantity();
        foreach ($inventoires AS $inventory) {
            if (!$this->hasQuantityForRecipeComponent($compo)) {
                $sum += $inventory->getBasePrice() * $quantityNeeded;
                break;
            }
            if ($inventory->getBaseQuantity() >= $quantityNeeded) {
                $sum += $inventory->getBasePrice() * $quantityNeeded;
                break;
            } else {
                $reste = $quantityNeeded - $inventory->getBaseQuantity();
                $sum += ($quantityNeeded - $reste) * $inventory->getBasePrice();
                $quantityNeeded = $reste;
            }
        }
        return $sum;
    }

    public function hasQuantityForRecipeComponent(IRecipeComponent $compo)
    {
        $qb = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->leftJoin('i.unit', 'unit')
            ->select('SUM(i.quantity*unit.parentRatio)')
            ->where('i.user = :user')
            ->andWhere('i.component = :component')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->setParameter('component', $compo->getComponent())
            ->groupBy('i.user');
        if ($compo->getOptionLabel() != null) {
            $qb->andWhere($qb->expr()->eq('i.optionLabel', ':option'))
                ->setParameter('option', $compo->getOptionLabel());
        } else {
            $qb->andWhere($qb->expr()->isNull('i.optionLabel'));
        }


        $result = $qb->getQuery()->getOneOrNullResult();


// compos quantity = 500 g
// compos base_quantity = 500 g / 1000 = 0.5Kg
// inventaire = 0.500 kg
        $res = is_array($result) ? array_pop($result) : 0;
        return $res >= $compo->getBaseQuantity();
    }

    public function sub(IRecipeComponent $recipeComponent, int $quantity)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($this->tokenStorage->getUser());

        /** @var Inventory[] $inventories */
        $qb = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->leftJoin('i.unit', 'unit')
            ->where('i.user = :user')
            ->andWhere('i.component = :component')
            ->setParameter('user', $this->tokenStorage->getUser())
            ->setParameter('component', $recipeComponent->getComponent())
            ->orderBy('i.price', $user->getUseOrderPreference());

        if ($recipeComponent->getOptionLabel() != null) {
            $qb->andWhere($qb->expr()->eq('i.optionLabel', ':option'))
                ->setParameter('option', $compo->getOptionLabel());
        } else {
            $qb->andWhere($qb->expr()->isNull('i.optionLabel'));
        }


        $inventories = $qb->getQuery()->getResult();
        // compos quantity = 500 g
        //inventaire = 0.500 kg

        $quantityNeeded = $quantity;
        foreach ($inventories AS $inventory) {
            $qty = $inventory->getQuantity();
            if ($qty == $quantityNeeded) {
                $inventory->setQuantity(0);
                $this->entityManager->flush();
                return true;
            } elseif ($qty > $quantityNeeded) {
                $inventory->setQuantity($qty - $quantityNeeded);
                $this->entityManager->flush();
                return true;
            } else {
                $inventory->setQuantity(0);
                $quantityNeeded = $quantityNeeded - $qty;
                $this->entityManager->flush();
            }

            if ($quantityNeeded == 0) {
                return true;
            }
        }
        return false;
    }

}