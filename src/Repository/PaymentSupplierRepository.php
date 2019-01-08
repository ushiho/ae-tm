<?php

namespace App\Repository;

use App\Entity\PaymentSupplier;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

     public function findOneByPaymentAndDate($payment, $date){
         $req = $this->createQueryBuilder('ps')
                    ->select('price')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'ps.payment = p')
                    ->andWhere('p = :val')
                    ->setParameter('val', $payment)
                    ->andWhere('ps.datePayment = :date')
                    ->setParameter('date', $date)
                    ->getQuery();
        dd($req);
     }

    //  public function editAllAmounts($payment, $price){
    //      return $this->createQueryBuilder('ps')
    //                 ->upsate('App:PaymentSupplier', 'ps')
    //                 ->set('ps.totalPricePaid', 'ps.totalPricePaid - '.$price)
    //                 ->set('ps.remainingPrice', 'ps.remainingPrice + '.$price)
    //                 ->getQuery()
    //                 ->execute();
    //     // return $req;
    //  }

    public function generateWhere($query, $data)
    {
        $cond = " 1=1 ";
        if ($data['startDate'] !== null && $data['endDate'] !== null) {
            $cond .= 'AND ps.datePayment BETWEEN :firstDate AND :scondDate';
            $query->setParameter('firstDate', $data['startDate'])
                ->setParameter('scondDate', $data['endDate']);
        }
        if($data['project']){
            $cond .= ' AND pr = :project';
            $query->setParameter('project', $data['project']);
        }

        return $query->where($cond)
        ->orderBy('ps.datePayment', 'DESC')
        ->getQuery();
    }

    public function findByProjectAndDate($data){
        $query = $this->createQueryBuilder('ps')
                    ->select('ps')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'ps.payment = p')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'm.payment = p')
                    ->innerJoin('App:Project', 'pr', Join::WITH, 'm.project = pr');
        
        return $this->generateWhere($query, $data)
                    ->getResult();
                    
    }
}
