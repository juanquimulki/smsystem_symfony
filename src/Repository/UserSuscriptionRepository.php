<?php

namespace App\Repository;

use App\Entity\UserSuscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<UserSuscription>
 *
 * @method UserSuscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSuscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSuscription[]    findAll()
 * @method UserSuscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSuscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSuscription::class);
    }

//    /**
//     * @return UserSuscription[] Returns an array of UserSuscription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

   public function findOneByUserSuscription($user_id, $suscription_id): ?UserSuscription
   {
       return $this->createQueryBuilder('us')
           ->andWhere('us.user_id = :userId')
           ->andWhere('us.suscription_id = :suscriptionId')
           ->setParameter('userId', $user_id)
           ->setParameter('suscriptionId', $suscription_id)
           ->getQuery()
           ->getOneOrNullResult()
       ;
   }

   public function findAllByUserAsArray($user_id): array
   {
       return $this->createQueryBuilder('us')
           ->andWhere('us.user_id = :userId')
           ->setParameter('userId', $user_id)
           ->getQuery()
           ->getResult(Query::HYDRATE_ARRAY)
       ;
   }
}
