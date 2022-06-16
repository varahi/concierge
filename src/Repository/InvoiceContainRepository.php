<?php

namespace App\Repository;

use App\Entity\InvoiceContain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InvoiceContain|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceContain|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceContain[]    findAll()
 * @method InvoiceContain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceContainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceContain::class);
    }

    /**
     * @param InvoiceContain $contain
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteContainNative(
        InvoiceContain $contain
    ): void {
        $this->getEntityManager()->getConnection()->delete('invoice_contain', array('id' => $contain->getId()));
    }

    // /**
    //  * @return InvoiceContain[] Returns an array of InvoiceContain objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InvoiceContain
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
