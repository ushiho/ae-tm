<?php

namespace App\Repository;

use App\Entity\GasStation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GasStation|null find($id, $lockMode = null, $lockVersion = null)
 * @method GasStation|null findOneBy(array $criteria, array $orderBy = null)
 * @method GasStation[]    findAll()
 * @method GasStation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GasStationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GasStation::class);
    }

//    /**
//     * @return GasStation[] Returns an array of GasStation objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GasStation
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
