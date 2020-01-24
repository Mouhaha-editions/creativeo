<?php


namespace App\Service;


use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\VarDumper\VarDumper;

class InventoryService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function countTotalPrice(UserInterface $user)
    {
        $result = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->select('SUM(i.quantity*i.price)')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->groupBy('i.user')
            ->getQuery()->getOneOrNullResult();

        $res = is_array($result) ? array_pop($result): null;
       return $res == null ? 0 : $res;
    }
    public function countQuantity(UserInterface $user)
    {
        $result = $this->entityManager->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->groupBy('i.user')
            ->getQuery()->getOneOrNullResult();
        $res = is_array($result) ? array_pop($result): null;
        return $res == null ? 0 : $res;

    }
}