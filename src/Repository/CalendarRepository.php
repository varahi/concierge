<?php

namespace App\Repository;

use App\Entity\Calendar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Calendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendar[]    findAll()
 * @method Calendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarRepository extends ServiceEntityRepository
{
    /**
     *
     */
    private const DAYS_BEFORE_REMOVAL = 7;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendar::class);
    }

    /**
     * @return float|int|mixed|string
     */
    public function findByStartDate()
    {
        $limit = 9999;
        $offset = 0;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('App\Entity\Calendar', 'e')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('e.startDate', 'ASC');

        $calendars = $qb->getQuery()->getResult();

        return $calendars;
    }

    /**
     * @param array $array
     * @return float|int|mixed|string
     */
    public function findByTasks(array $array)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c');
        $qb->from('App\Entity\Calendar', 'c');
        $qb->where($qb->expr()->in('c.id', $array));
        $qb->orderBy('c.startDate', 'ASC');
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function _findByEmptyTasks()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c')
            ->from($this->_entityName, 'c')
            ->andWhere('c.endDate < :date')
            ->orderBy('c.id', 'ASC')
            ->setParameters([
                'date' => new \DateTime(-self::DAYS_BEFORE_REMOVAL.' days'),
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function findByEmptyTasks()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c')
            ->from($this->_entityName, 'c')
            ->where('c.renter is NULL')
            ->andWhere('c.endDate < :date')
            ->orderBy('c.id', 'ASC')
            ->setParameters([
                'date' => new \DateTime(-self::DAYS_BEFORE_REMOVAL.' days'),
            ])
        ;

        return $qb->getQuery()->getResult();
    }



    // /**
    //  * @return Calendar[] Returns an array of Calendar objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Calendar
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
