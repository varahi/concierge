<?php

namespace App\Repository;

use App\Entity\Renter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Renter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Renter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Renter[]    findAll()
 * @method Renter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Renter::class);
    }

    // /**
    //  * @return Renter[] Returns an array of Renter objects
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
    public function findOneBySomeField($value): ?Renter
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
