<?php

namespace App\Repository;

use App\Entity\EspaceDeCoworking;
use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;



/**
 * @method EspaceDeCoworking|null find($id, $lockMode = null, $lockVersion = null)
 * @method EspaceDeCoworking|null findOneBy(array $criteria, array $orderBy = null)
 * @method EspaceDeCoworking[]    findAll()
 * @method EspaceDeCoworking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EspaceDeCoworkingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EspaceDeCoworking::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EspaceDeCoworking $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    /**
     * @return EspaceDeCoworking[]
     */
    public function findLast(): array
    {
        return $this->createQueryBuilder('e')
        ->orderBy('e.id','DESC')
        ->setMaxResults(4)
        ->getQuery()
        ->getResult();
    }

        private function findVisibleQuery(): QueryBuilder
    {
      
        return $this->createQueryBuilder('e');
              
    }
    /**
     * @return Query
     */
    public function findAllVisibleQuery(EspaceSearch $search): Query
    {
        $query = $this->findVisibleQuery();
        if ($search->getMaxPrice()){
            $query = $query 
                    -> where('e.prix <= :maxprice')
                    -> setParameter('maxprice', $search->getMaxPrice());
        }
        return $query->getQuery();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(EspaceDeCoworking $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EspaceDeCoworking[] Returns an array of EspaceDeCoworking objects
    //  */
    

    public function SearchEspace($criteria)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.ville = :villeName')
            ->setParameter('villeName', $criteria['ville'])
            ->andWhere('v.prix <= :prix')
            ->setParameter('prix', $criteria['prix'])
            ->andWhere('v.imprimante = :imprimante')
            ->setParameter('imprimante', $criteria['imprimante'])
            ->andWhere('v.parking = :parking')
            ->setParameter('parking', $criteria['parking'])
            ->andWhere('v.cafe = :cafe')
            ->setParameter('cafe', $criteria['cafe'])
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?EspaceDeCoworking
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
