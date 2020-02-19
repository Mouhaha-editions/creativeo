<?php

namespace App\Repository;

use App\Entity\RecipeFabricationComponent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RecipeFabricationComponent|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeFabricationComponent|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeFabricationComponent[]    findAll()
 * @method RecipeFabricationComponent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeFabricationComponentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeFabricationComponent::class);
    }

    // /**
    //  * @return RecipeFabricationComponent[] Returns an array of RecipeFabricationComponent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RecipeFabricationComponent
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
