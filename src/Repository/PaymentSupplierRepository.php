<?php

namespace App\Repository;

use App\Entity\PaymentSupplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PaymentSupplier|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentSupplier|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentSupplier[]    findAll()
 * @method PaymentSupplier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentSupplierRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentSupplier::class);
    }

//    /**
//     * @return PaymentSupplier[] Returns an array of PaymentSupplier objects
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

    // public function findByMission($mission): ?PaymentSupplier
    // {
    //     return $this->createQueryBuilder('p')
    //         ->andWhere('p.allocate = :val')
    //         ->setParameter('val', $mission->getAllocate())
    //         ->getQuery()
    //         ->getOneOrNullResult()
    //     ;
    // }

    public function findByMission($mission)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.allocate = :val')
            ->setParameter('val', $mission->getAllocate())
            ->getQuery()
            ->getResult();
    }


    //Joint two tables: allocate & paymentSupplier where rent.idSupplier = paymentSupplier.idSupplier
    public function findByPayment($payment){
        return $this->createQueryBuilder('p')
                    ->andWhere('p.payment = :val')
                    ->setParameter('val', $payment)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Set an array in Query builder
     * $builder->andWhere('type IN (:string)');
     * $builder->setParameter('string', array('first','second'), \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
     */

}
