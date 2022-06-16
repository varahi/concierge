<?php

namespace App\Repository;

use App\Entity\Services;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Services|null find($id, $lockMode = null, $lockVersion = null)
 * @method Services|null findOneBy(array $criteria, array $orderBy = null)
 * @method Services[]    findAll()
 * @method Services[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicesRepository extends ServiceEntityRepository
{
    public const SERVICE_TABLE = 'App\Entity\Services';

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Services::class);
    }

    /**
     * @param array $order
     * @return Services[]
     */
    public function findAllOrder(array $order)
    {
        return $this->findBy([], $order);
    }

    /**
     * @param int $id
     * @return int|mixed|string
     */
    public function findByRenter(int $id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s');
        $qb->from(self::SERVICE_TABLE, 's');
        $qb->join('s.renters', 'r');
        $qb->where($qb->expr()->eq('r.id', $id));

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Services[] Returns an array of Services objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Services
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
