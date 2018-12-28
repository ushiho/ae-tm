<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Mission::class);
    }

//    /**
//     * @return Mission[] Returns an array of Mission objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mission
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    // $query = $em->createQuery('DELETE SomeOtherBundle:CarEntityClass c WHERE c.idOwner = 4 AND c.id = 10');

    public function findMissionByStateByDriver($driver, $finished)
    {
        return $this->createQueryBuilder('m')
                ->andWhere('m.driver = :driver')
                ->andWhere('m.finished = :finished')
                ->setParameter('driver', $driver)
                ->setParameter('finished', $finished)
                ->getQuery()
                ->getResult()
            ;
    }

    public function findByDepartment($department)
    {
        return $this->createQueryBuilder('m')
        ->andWhere('m.department = :department')
        ->setParameter('department', $department)
        ->getQuery()
        ->getResult()
        ;
    }

    public function findByProject($project)
    {
        return $this->createQueryBuilder('m')
        ->andWhere('m.project = :project')
        ->setParameter('project', $project)
        ->getQuery()
        ->getResult()
        ;
    }

    public function findByDriver($driver)
    {
        return $this->createQueryBuilder('m')
        ->andWhere('m.driver = :driver')
        ->setParameter('driver', $driver)
        ->getQuery()
        ->getResult()
        ;
    }

    public function updateMissionTable()
    {
        return $this->createQueryBuilder('m')
                    ->update('App:Mission', 'm')
                    ->set('m.finished', '1')
                    ->where('m.endDate <= CURRENT_DATE()')
                    ->getQuery()
                    ->execute();
    }

    public function findByRent(Allocate $rent)
    {
        return $this->createQueryBuilder('m')
                    ->andWhere('m.allocate = :val')
                    ->setParameter('val', $rent)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findByDriverAndFinishedState($driver)
    {
        return $this->createQueryBuilder('m')
                    ->andWhere('m.driver = :driver')
                    ->setParameter('driver', $driver)
                    ->andWhere('m.finished = false')
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findByVehicleAndFinishedState($vehicle)
    {
        return $this->createQueryBuilder('m')
                    ->innerJoin('App:Allocate', 'rent', Join::WITH, 'm.allocate = rent')
                    ->innerJoin('App:Vehicle', 'v', Join::WITH, 'rent.vehicle = v')
                    ->andWhere('m.finished = false')
                    ->andWhere('v = :vehicle')
                    ->setParameter('vehicle', $vehicle)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
