<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByParticipatingUser(User $user)
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('e.participants', 'u')  // Join on the participants relationship
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId());

        return $qb->getQuery()->getResult();
    }

//    public function findByCampus(?int $campusId): array
//    {
//        $qb = $this->createQueryBuilder('e');
//
//        if (!is_null($campusId)) {
//            $qb->andWhere('e.campus = :campusId')
//                ->setParameter('campusId', $campusId);
//        }
//
//        return $qb->getQuery()->getResult();
//    }

    public function findByFilters(?int $campusId = null, ?int $organizerId = null, ?int $categoryId = null, ?int $statusId =null, ?int $userId= null): array
    {
        $qb = $this->createQueryBuilder('e');

        if (!is_null($campusId)) {
            $qb->andWhere('e.campus = :campusId')
                ->setParameter('campusId', $campusId);
        }

        if (!is_null($organizerId)) {
            $qb->andWhere('e.organizer = :organizerId')
                ->setParameter('organizerId', $organizerId);
        }

        if (!is_null($categoryId)) {
            // Si c'est une relation ManyToMany
            $qb->join('e.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if (!is_null($statusId)) {
            $qb->andWhere('e.status = :statusId')
                ->setParameter('statusId', $statusId);

            // Vérifier si le statut est "Brouillon"
            $status = $this->getEntityManager()->getRepository(Status::class)->find($statusId);
            if ($status && strtolower($status->getName()) === 'brouillon') {
                if (!is_null($userId)) {
                    $qb->andWhere('e.organizer = :userId')
                        ->setParameter('userId', $userId);
                } else {
                    // Personne ne doit voir les brouillons s'il n'est pas connecté
                    $qb->andWhere('1 = 0');
                }
            }
        }


        return $qb->getQuery()->getResult();
    }



}
