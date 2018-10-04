<?php

namespace App\Repository;

use App\Entity\PaymentDriver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PaymentDriver|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentDriver|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentDriver[]    findAll()
 * @method PaymentDriver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentDriverRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentDriver::class);
    }

//    /**
//     * @return PaymentDriver[] Returns an array of PaymentDriver objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentDriver
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
