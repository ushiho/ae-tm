<?php

namespace App\Repository;

use App\Entity\Driver;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Driver|null find($id, $lockMode = null, $lockVersion = null)
 * @method Driver|null findOneBy(array $criteria, array $orderBy = null)
 * @method Driver[]    findAll()
 * @method Driver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Driver::class);
    }

//    /**
//     * @return Driver[] Returns an array of Driver objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Driver
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByType($type)
    {
        return $this->createQuery('d')
        ->andWhere('d.vehicleType = :type')
        ->setParameter('type', $type)
        ->getQuery()
        ->getResult()
        ;
    }

    public function findByConditionOnMission($condition)
    {
        return $this->createQueryBuilder('d')
        ->andWhere('d.missions = :condition')
        ->setParameter('condition', $condition)
        ->getQuery()
        ->getResult()
        ;
    }

    public function findByMission($mission)
    {
        return $this->createQueryBuilder('d')
                ->innerJoin('App:Mission', 'm', Join::WITH, 'm.driver = d')
                ->andWhere('m = :val')
                ->setParameter('val', $mission)
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function updateDriverTable()
    {
        // Raw SQL
        $conn = $this->getEntityManager()->getConnection();
        $req1 = 'UPDATE driver d SET d.busy = 1 WHERE d.id IN ( SELECT m.driver_id from mission m WHERE m.finished = 0 )';
        $req2 = 'UPDATE driver d SET d.busy = 0 WHERE d.id IN ( SELECT m.driver_id from mission m WHERE m.finished = 1 )';
        $conn->prepare($req1)->execute();
        $conn->prepare($req2)->execute();
    }

    public function findByCriteria($data)
    {
        return $this->createQueryBuilder('d')
                    ->orWhere('1 = 1')
                    ->orWhere('d.firstName = :first')
                    ->setParameter('first', $data['firstName'])
                    ->orWhere('d.lastName = :last')
                    ->setParameter('last', $data['lastName'])
                    ->orWhere('d.cin = :cin')
                    ->setParameter('cin', $data['cin'])
                    ->groupBy('d.lastName')
                    ->getQuery()
                    ->getResult();
    }
}
