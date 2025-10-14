<?php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Place>
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function calculateOccupancyRate(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): float
    {
        $entityManager = $this->getEntityManager();

        // Nombre total de places
        $totalPlaces = $this->count([]);

        if ($totalPlaces === 0) {
            return 0.0; // Éviter la division par zéro
        }

        // Nombre de places réservées dans la période donnée
        $query = $entityManager->createQuery(
            'SELECT COUNT(DISTINCT p.id) FROM App\Entity\Place p
             JOIN p.reservations r
             WHERE r.startDate < :endDate AND r.endDate > :startDate'
        )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $reservedPlaces = (int) $query->getSingleScalarResult();

        // Calcul du taux d\'occupation
        return ($reservedPlaces / $totalPlaces) * 100;
    }

    //    /**
    //     * @return Place[] Returns an array of Place objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Place
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
