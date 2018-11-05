<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Payment::class);
    }

//    /**
//     * @return Payment[] Returns an array of Payment objects
//     */
    
    public function findByMission($mission)
    {
        return $this->createQueryBuilder('p')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'p = m.payment')
                    ->andWhere('m = :val')
                    ->setParameter('val', $mission)
                    ->getQuery()
                    ->getResult();
    }

    public function findByProject($project)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.project = :val')
            ->setParameter('val', $project)
            ->orderBy('p.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    /*
    public function findOneBySomeField($value): ?Payment
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
