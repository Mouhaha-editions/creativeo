<?php

namespace App\Repository;

use App\Entity\RecipeFabrication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RecipeFabrication|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeFabrication|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeFabrication[]    findAll()
 * @method RecipeFabrication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeFabricationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeFabrication::class);
    }

    // /**
    //  * @return RecipeFabrication[] Returns an array of RecipeFabrication objects
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
    public function findOneBySomeField($value): ?RecipeFabrication
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
