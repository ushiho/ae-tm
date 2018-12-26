<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

//    /**
//     * @return Vehicle[] Returns an array of Vehicle objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vehicle
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByType($type)
    {
        return $this->createQueryBuilder('v')
        ->andWhere('v.type = :type')
        ->setParameter('type', $type)
        ->getQuery()
        ->getResult()
        ;
    }

    public function findByCriteria($data)
    {
        return $this->createQueryBuilder('v')
                    ->orWhere('v.brand = :brand')
                    ->setParameter('brand', $data['brand'])
                    ->orWhere('v.matricule = :mat')
                    ->setParameter('mat', $data['matricule'])
                    ->getQuery()
                    ->getResult();
    }
}
