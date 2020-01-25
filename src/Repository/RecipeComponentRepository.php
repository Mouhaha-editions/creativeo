<?php

namespace App\Repository;

use App\Entity\RecipeComponent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RecipeComponent|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeComponent|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeComponent[]    findAll()
 * @method RecipeComponent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeComponentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeComponent::class);
    }

    // /**
    //  * @return RecipeComponent[] Returns an array of RecipeComponent objects
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
    public function findOneBySomeField($value): ?RecipeComponent
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
