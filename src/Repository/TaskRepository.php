<?php

namespace App\Repository;

use App\Entity\Calendar;
use App\Entity\Housing;
use App\Entity\Renter;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    /**
     *
     */
    public const TASK_TABLE = 'App\Entity\Task';

    public const CALENDAR_TABLE = 'App\Entity\Calendar';

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }


    /**
     * @return int|mixed|string
     */
    public function findEmptyTitle()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->where('t.title is NULL')
            ->orWhere('t.title LIKE :title')
            ->setParameters([
                'title' => '',
            ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return int|mixed|string
     */
    public function findByUser(int $id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t');
        $qb->from(self::TASK_TABLE, 't');
        $qb->join('t.users', 'u');
        $qb->where($qb->expr()->eq('u.id', $id));

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return int|mixed|string
     */
    public function findByApartmentNotArchived(int $id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->join('t.housing', 'h')
            ->where($qb->expr()->eq('h.id', $id))
            ->andWhere('t.isArchived is not NULL');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return int|mixed|string
     */
    public function findByApartment(int $id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->join('t.housing', 'h')
            ->where($qb->expr()->eq('h.id', $id));

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int|mixed|string
     */
    public function findWithRetner()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->where('t.renter is not NULL');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int|mixed|string
     */
    public function findSingle()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->where('t.isSingle is not NULL')
            ->andWhere($qb->expr()->eq('t.isSingle', 1));

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Task $task
     */
    public function findRelaytedTask(Task $task)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        if ($task->getCalendar()->getId() == null) {
            $qb->select('t')
                ->from(self::TASK_TABLE, 't')
                ->where($qb->expr()->eq('t.housing', $task->getHousing()->getId()))
                ->andWhere($qb->expr()->eq('t.renter', $task->getRenter()->getId()))
                ->andWhere($qb->expr()->neq('t.id', $task->getId()));
        } else {
            $qb->select('t')
                ->from(self::TASK_TABLE, 't')
                ->where($qb->expr()->eq('t.housing', $task->getHousing()->getId()))
                ->andWhere($qb->expr()->neq('t.id', $task->getId()));
        }

        if ($task->getCalendar()) {
            $qb->andWhere($qb->expr()->eq('t.calendar', $task->getCalendar()->getId()));
        }

        if ($task->getRenter()) {
            $qb->andWhere($qb->expr()->eq('t.renter', $task->getRenter()->getId()));
        }

        if ($task->getInvoice()) {
            $qb->andWhere($qb->expr()->eq('t.invoice', $task->getInvoice()->getId()));
        }

        if (count($qb->getQuery()->getResult()) <= 1) {
            return $qb->getQuery()->getOneOrNullResult();
        } else {
            //return null;
            //$qb->setMaxResults(1);
            //return $qb->getQuery()->getSingleColumnResult();
            //return $qb->getQuery()->getOneOrNullResult();
            return $qb->getQuery()->getResult();
        }
    }

    /**
     * @param Housing $housing
     * @param Calendar $calendar
     * @param Renter $renter
     */
    public function _findRelaytedTask_will_be_removed(Housing $housing, Calendar $calendar, Renter $renter, $currentTask)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if ($calendar->getId() == null) {
            $qb->select('t')
                ->from(self::TASK_TABLE, 't')
                ->where($qb->expr()->eq('t.housing', $housing->getId()))
                ->andWhere($qb->expr()->eq('t.renter', $renter->getId()))
                ->andWhere($qb->expr()->neq('t.id', $currentTask));
        } else {
            $qb->select('t')
                ->from(self::TASK_TABLE, 't')
                ->where($qb->expr()->eq('t.housing', $housing->getId()))
                ->andWhere($qb->expr()->eq('t.calendar', $calendar->getId()))
                ->andWhere($qb->expr()->eq('t.renter', $renter->getId()))
                ->andWhere($qb->expr()->neq('t.id', $currentTask));
        }

        // If is invoice for task
        if ($currentTask) {
            //$qb->join('t.invoice', 'i')->andWhere($qb->expr()->eq('i.id', $currentTask));
            //$qb->andWhere($qb->expr()->eq('t.invoice', $currentTask));
        }

        if (count($qb->getQuery()->getResult()) <= 1) {
            return $qb->getQuery()->getOneOrNullResult();
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    /**
     * @param array $order
     * @return Task[]
     */
    public function findAllOrder(array $order)
    {
        return $this->findBy([], $order);
    }


    /**
     * @param string $order
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param $employeeId
     * @return float|int|mixed|string
     */
    public function findByDatesRangeAndForeignApartment(string $order, \DateTime $startDate, \DateTime $endDate, $employeeId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->leftJoin('t.housing', 'h')
            ->leftJoin('t.calendar', 'c')
            ->where('c.startDate > :startDate')
            ->andWhere('c.startDate < :endDate')
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            //->orderBy('h.name', $order)
            ->orderBy('t.startDate', $order)
        ;

        if ($employeeId && is_array($employeeId)) {
            //$qb->andWhere($qb->expr()->eq('t.renter', $renterId));
            $qb->join('t.users', 'u')
                ->andWhere($qb->expr()->in('u.id', $employeeId));
        }

        /*
         * // if will be chosen is event tasks
        if($isCalendar === false) {
            $qb->andWhere('t.isEvent is NULL');
            $qb->orWhere($qb->expr()->eq('t.isEvent', 0));
        } else {
            $qb->andWhere('t.isEvent is not NULL');
            $qb->andWhere($qb->expr()->eq('t.isEvent', 1));
        }
        */

        $result = $qb->getQuery()->getResult();
        return $result;
    }


    /**
     * @param string $order
     * @param string $fieldName
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return int|mixed|string
     */
    public function findByDatesRangeAndForeignApartmentSorting(string $order, string $fieldName, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(self::TASK_TABLE, 't')
            ->leftJoin('t.housing', 'h')
            ->leftJoin('t.calendar', 'c')
            ->where('c.startDate > :startDate')
            ->andWhere('c.startDate < :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('c.' . $fieldName, $order)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }


    /**
     * @param $order
     * @return int|mixed|string
     */
    public function findAllOrderByForeignApartment($order)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.housing', 'h')
            ->orderBy('h.name', $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $order
     * @return int|mixed|string
     */
    public function findAllOrderByForeignApartmentDql($order)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.housing', 'h')
            ->orderBy('h.name', $order)
            ->getQuery();
    }

    /**
     * @param Task $task
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteTaskNative(
        Task $task
    ): void {
        $this->getEntityManager()->getConnection()->delete('task', array('id' => $task->getId()));
    }



    // /**
    //  * @return Task[] Returns an array of Task objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
