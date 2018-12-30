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

    public function getReconciliations($driverID, $vehicleID, $departmentID, $projectID, $firstDate, $scondDate, $isPaid, $gasStation)
    {
        $query = $this->createQueryBuilder('fr')
            ->select('fr')
            ->leftJoin('fr.driver', 'driver')
            ->leftJoin('fr.vehicle', 'vehicle')
            ->leftJoin('fr.department', 'department')
            ->leftJoin('fr.project', 'project')
            ->leftJoin('fr.gasStation', 'gasStation');
        $queryRes = $this->generateWhere($driverID, $vehicleID, $departmentID, $projectID, $firstDate, $scondDate, $isPaid, $gasStation, $query);

        return $queryRes->getResult();
    }

    public function getDriversName()
    {
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT d.lastName')
            ->orderBy('d.lastName', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getSubTotals()
    {
        $query = $this->createQueryBuilder('fr')
            ->select('SUM(fr.totalAmount) as totalAmount ,SUM(fr.totalLitres) as totalLiters , driver.lastName, driver.firstName')
            ->leftJoin('fr.driver', 'driver')
            ->groupBy('driver.lastName')
            ->orderBy('driver.lastName', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getTotal()
    {
        $query = $this->createQueryBuilder('fr')
            ->select('SUM(fr.totalAmount) as totalAmount ')
            ->getQuery();

        return $query->getResult();
    }

    public function generateWhere($driverID, $vehicleID, $departmentID, $projectID, $firstDate, $scondDate, $isPaid, $gasStation, $query)
    {
        $conds = '1=1 ';
        if ($driverID !== 0) {
            $conds .= 'AND driver.id = ?1';
            $query->setParameter(1, $driverID);
        }
        if ($vehicleID !== 0) {
            $conds .= 'AND vehicle.id = ?2';
            $query->setParameter(2, $vehicleID);
        }
        if ($departmentID !== 0) {
            $conds .= 'AND department.id = ?3';
            $query->setParameter(3, $departmentID);
        }
        if ($isPaid !== null) {
            $conds .= 'AND fr.isPaid = :isPaid ';
            $query->setParameter('isPaid', $isPaid);
        }
        if ($gasStation) {
            $conds .= 'AND gasStation.id = :gasStation ';
            $query->setParameter('gasStation', $gasStation->getId());
        }
        if ($projectID !== 0) {
            $conds .= 'AND project.id = ?4';
            $query->setParameter(4, $projectID);
        }
        if ($firstDate !== null && $scondDate !== null) {
            $conds .= 'AND fr.createdAt BETWEEN :firstDate AND :scondDate';
            $query->setParameter('firstDate', $firstDate->format('Y-m-d'))
            ->setParameter('scondDate', $scondDate->format('Y-m-d'));
        }

        $res = $query->where($conds)
        ->orderBy('driver.lastName', 'DESC')
        ->getQuery();

        return $res;
    }

    public function findReconciliationsByIds($ids)
    {
        $qb = $this->createQueryBuilder('fr');
        $qb
            ->select('fr')
            ->leftJoin('fr.driver', 'driver')
            ->leftJoin('fr.vehicle', 'vehicle')
            ->leftJoin('fr.department', 'department')
            ->leftJoin('fr.project', 'project')
            ->where($qb->expr()->in('fr.id', ':ids'))
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
