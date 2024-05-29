<?php

namespace App\Repository;

use App\Entity\Userauth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Userauth>
 *
 * @method Userauth|null find($id, $lockMode = null, $lockVersion = null)
 * @method Userauth|null findOneBy(array $criteria, array $orderBy = null)
 * @method Userauth[]    findAll()
 * @method Userauth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserauthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Userauth::class);
    }

    //    /**
    //     * @return Userauth[] Returns an array of Userauth objects
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

    //    public function findOneBySomeField($value): ?Userauth
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
