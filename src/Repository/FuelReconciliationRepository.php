<?php

namespace App\Repository;

use App\Entity\FuelReconciliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FuelReconciliation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FuelReconciliation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FuelReconciliation[]    findAll()
 * @method FuelReconciliation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FuelReconciliationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FuelReconciliation::class);
    }

//    /**
//     * @return FuelReconciliation[] Returns an array of FuelReconciliation objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FuelReconciliation
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
