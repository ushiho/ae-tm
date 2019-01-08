<?php

namespace App\Repository;

use App\Entity\PaymentDriver;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function findByMission($mission){
        return $this->createQueryBuilder('pd')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'pd.payment = p')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'p.mission = m')
                    ->andWhere('m = :val')
                    ->setParameter('val', $mission)
                    ->getQuery()
                    ->getResult();
    }

    public function findByProject($project){
        return $this->createQueryBuilder('pd')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'pd.payment = p')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'p.mission = m')
                    ->innerJoin('App:Project', 'pr', Join::WITH, 'm.project = pr')
                    ->andWhere('pr = :val')
                    ->setParameter('val', $project)
                    ->getQuery()
                    ->getResult();
    }

    public function findByPayment($payment){
        return $this->createQueryBuilder('pd')
                    ->andWhere('pd.payment = :val')
                    ->setParameter('val', $payment)
                    ->getQuery()
                    ->getResult();
    }

    public function findOneByPayment($payment)
    {
        return $this->createQueryBuilder('pd')
            ->innerJoin('App:Payment', 'p', Join::WITH, 'pd.payment = p')
            ->andWhere('p = :val')
            ->setParameter('val', $payment)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function generateWhere($query, $data)
    {
        $cond = " 1=1 ";
        if ($data['startDate'] !== null && $data['endDate'] !== null) {
            $cond .= 'AND pd.datePayment BETWEEN :firstDate AND :scondDate';
            $query->setParameter('firstDate', $data['startDate'])
                ->setParameter('scondDate', $data['endDate']);
        }
        if($data['project']){
            $cond .= ' AND pr = :project';
            $query->setParameter('project', $data['project']);
        }

        return $query->where($cond)
        ->orderBy('pd.datePayment', 'DESC')
        ->getQuery();
    }

    public function findByProjectAndDate($data){
        $query = $this->createQueryBuilder('pd')
                    ->select('pd')
                    ->innerJoin('App:Payment', 'p', Join::WITH, 'pd.payment = p')
                    ->innerJoin('App:Mission', 'm', Join::WITH, 'm.payment = p')
                    ->innerJoin('App:Project', 'pr', Join::WITH, 'm.project = pr');
        
        return $this->generateWhere($query, $data)
                    ->getResult();
                    
    }
}
