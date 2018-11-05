<?php

namespace App\Repository;

use App\Entity\Supplier;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Mission;

/**
 * @method Supplier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supplier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supplier[]    findAll()
 * @method Supplier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplierRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Supplier::class);
    }

//    /**
//     * @return Supplier[] Returns an array of Supplier objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Supplier
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByMission(Mission $mission){
        return $this->createQueryBuilder('s')
                    ->innerJoin('App:Allocate', 'rent', Join::WITH, 's = rent.supplier')
                    ->innerJoin('App:Mission', 'mission', Join::WITH, 'rent = mission.allocate')
                    ->andWhere('mission = :val')
                    ->setParameter('val', $mission)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
