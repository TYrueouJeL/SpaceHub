<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getAverageNote(): ?float
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('AVG(r.rating) as avg_note');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    public function getAverageNoteByPlaceId(int $placeId): ?float
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('AVG(r.rating) as avg_note')
            ->where('r.place = :placeId')
            ->setParameter('placeId', $placeId);

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    //    /**
    //     * @return Review[] Returns an array of Review objects
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

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
