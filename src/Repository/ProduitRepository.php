<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    //    /**
//     * @return Produit[] Returns an array of Produit objects
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

    //    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function filter($data)
    {
        dd('filter called');
        $qb = $this->createQueryBuilder('p');

        if ($data['auteur']) {
            $qb->andWhere('p.auteur = :auteur')
                ->setParameter('auteur', $data['auteur']);
        }

        if ($data['prix'] && strpos($data['prix'], '-') !== false) {
            [$min, $max] = explode('-', $data['prix']);
            $qb->andWhere('p.prix BETWEEN :min AND :max')
                ->setParameter('min', $min)
                ->setParameter('max', $max);
        }

        if ($data['date']) {
            $qb->andWhere('SUBSTRING(p.date, 1, 10) = :date')
                ->setParameter('date', $data['date']->format('Y-m-d'));
        }

        dump($qb->getQuery()->getSQL());
        dump($qb->getQuery()->getParameters());

        return $qb->getQuery()->getResult();
    }
}

