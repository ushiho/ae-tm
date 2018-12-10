<?php

namespace App\Repository;

use App\Entity\Allocate;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Allocate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Allocate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Allocate[]    findAll()
 * @method Allocate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllocateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Allocate::class);
    }

//    /**
//     * @return Allocate[] Returns an array of Allocate objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Allocate
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findBySupplier($supplier){
        return $this->createQueryBuilder('a')
        ->andWhere('a.supplier = :supplier')
        ->setParameter('supplier', $supplier)
        ->getQuery()
        ->getResult()
        ;
    }

    public function findOneByProject($project){
        return $this->createQueryBuilder('a')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'm.allocate = a')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'p.mission = m')
                    ->andWhere('p = :val')
                    ->setParameter('val', $project)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findOneByPayment($payment){
        return $this->createQueryBuilder('a')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'm.allocate = a')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'm.payment = p')
                    ->andWhere('p = :val')
                    ->setParameter('val', $payment)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
