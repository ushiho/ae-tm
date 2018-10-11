<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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

    public function findMissionByStateByDriver($driver, $finished){
        return $this->createQueryBuilder('m')
                ->andWhere('m.driver = :driver')
                ->andWhere('m.finished = :finished')
                ->setParameter('driver', $driver)
                ->setParameter('finished', $finished)
                ->getQuery()
                ->getResult()
            ;
    }

}
