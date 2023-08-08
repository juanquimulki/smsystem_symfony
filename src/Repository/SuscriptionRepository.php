<?php

namespace App\Repository;

use App\Entity\Suscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Suscription>
 *
 * @method Suscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Suscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Suscription[]    findAll()
 * @method Suscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suscription::class);
    }

//    /**
//     * @return Suscription[] Returns an array of Suscription objects
//     */
   public function findAllAsArray(): array
   {
       return $this->createQueryBuilder('s')
           ->orderBy('s.id', 'ASC')
           ->getQuery()
           ->getResult(Query::HYDRATE_ARRAY)
       ;
   }

//    public function findOneBySomeField($value): ?Suscription
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
