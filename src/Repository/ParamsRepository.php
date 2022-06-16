<?php

namespace App\Repository;

use App\Entity\Params;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Params|null find($id, $lockMode = null, $lockVersion = null)
 * @method Params|null findOneBy(array $criteria, array $orderBy = null)
 * @method Params[]    findAll()
 * @method Params[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParamsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Params::class);
    }

    public function findByIds(array $array)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c');
        $qb->from('App\Entity\Params', 'c');
        $qb->where($qb->expr()->in('c.id', $array));
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    // /**
    //  * @return Params[] Returns an array of Params objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Params
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
