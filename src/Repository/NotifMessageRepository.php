<?php

namespace App\Repository;

use App\Entity\NotifMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotifMessage>
 */
class NotifMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotifMessage::class);
    }

    public function findApprovedPublicMessages(string $roleString)
    {
        $rsm = new ResultSetMapping;
        $rsm->addEntityResult(NotifMessage::class, 'msg');
        $rsm->addFieldResult('msg', 'id', 'id');
        $rsm->addFieldResult('msg', 'message', 'message');
        $rsm->addFieldResult('msg', 'is_flagged', 'isFlagged');
        $rsm->addFieldResult('msg', 'roles', 'roles');
        $rsm->addFieldResult('msg', 'created_at', 'createdAt');

        $sql = "SELECT msg.id, msg.message, msg.is_flagged, msg.roles, msg.created_at FROM notif_message as msg
                LEFT JOIN user_notif_message as un ON msg.id = un.notif_message_id
                WHERE ISNULL(un.user_id)
                AND LOCATE(SUBSTRING(msg.roles, 3, CHAR_LENGTH(msg.roles)-4), ?) > 0
                ORDER BY msg.created_at DESC";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $roleString);

        return $query->getResult();
    }





    //    /**
    //     * @return NotifMessage[] Returns an array of NotifMessage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?NotifMessage
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
