<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_OWNER = 'ROLE_OWNER';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function findByRole($role)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.isVerified is not NULL')
            ->orderBy('u.company', 'ASC')
            ->addOrderBy('u.lastName', 'ASC')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function findByRoleAndHasApartments($role)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->leftJoin('u.apartments', 'apartment')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.isVerified is not NULL')
            ->andWhere('apartment.id is not NULL')
            ->orderBy('u.company', 'ASC')
            ->addOrderBy('u.lastName', 'ASC')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $role
     *
     * @return \Doctrine\ORM\Query
     */
    public function findByRoleDql($role)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.isVerified is not NULL')
            ->orderBy('u.company', 'ASC')
            ->addOrderBy('u.lastName', 'ASC')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery();
    }

    /**
     * @param string|null $string
     * @return User[] Returns an array of User objects
     */
    public function findOwnersOrApartments(string $string = null, $role)
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.firstName LIKE :firstName')
            ->orWhere('u.lastName LIKE :lastName')
            ->orWhere('u.company LIKE :company')
            ->andWhere('u.roles LIKE :roles')
            ->andWhere('u.isVerified is not NULL')
            ->join('u.apartments', 'appt')
            ->orWhere('appt.name LIKE :apptname')
            ->orWhere('appt.address LIKE :apptaddress')
            ->setParameters([
                'firstName' => '%'.$string.'%',
                'lastName' => '%'.$string.'%',
                'company' => '%'.$string.'%',
                'roles' => '%'.$role.'%',
                'apptname' => '%'.$string.'%',
                'apptaddress' => '%'.$string.'%',
            ])
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(100);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string|null $string
     * @return User[] Returns an array of User objects
     */
    public function findEmployers(string $string = null, $role)
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.firstName LIKE :firstName')
            ->orWhere('u.lastName LIKE :lastName')
            ->orWhere('u.company LIKE :company')
            ->andWhere('u.roles LIKE :roles')
            ->andWhere('u.isVerified is not NULL')
            ->setParameters([
                'firstName' => '%'.$string.'%',
                'lastName' => '%'.$string.'%',
                'company' => '%'.$string.'%',
                'roles' => '%'.$role.'%'
            ])
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(100);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $array
     * @return float|int|mixed|string
     */
    public function findByIds(array $array)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u');
        $qb->from('App\Entity\User', 'u');
        $qb->where($qb->expr()->in('u.id', $array));
        $result = $qb->getQuery()->getResult();

        return $result;
    }


    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
