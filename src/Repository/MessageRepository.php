<?php


namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }
    /**
     * @return Message[]
     */
    public function findAllMessagesBySection(int $idsection): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.sectionIdsection','s')
            ->Where('s.idsection = :val')
            ->setParameter('val', $idsection)
            ->orderBy('t.idmessage', 'DESC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}