<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function countBetween(\DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COUNT(r.id)')
            ->where('r.createdAt >= :start')
            ->andWhere('r.createdAt < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    // Récupère toutes les réservations, et fait multiplie la durée en jour par le prix de la place, puis fait la somme de tout ça
    public function sumTotalPrice(): float
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COALESCE(SUM(DATE_DIFF(r.endDate, r.startDate) * p.price), 0) AS total')
            ->join('r.place', 'p');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    public function sumTotalPriceBetween(\DateTimeImmutable $start, \DateTimeImmutable $end): float
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COALESCE(SUM(DATE_DIFF(r.endDate, r.startDate) * p.price), 0) AS total')
            ->join('r.place', 'p')
            ->where('r.createdAt >= :start')
            ->andWhere('r.createdAt < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
