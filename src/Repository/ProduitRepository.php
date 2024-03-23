<?php

// src/Repository/ProduitRepository.php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    // Méthode pour filtrer les produits en fonction des critères fournis
    public function findFilteredProducts($criteria)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if (isset ($criteria['id']) && $criteria['id'] !== null) {
            $queryBuilder
                ->andWhere('p.id = :id')
                ->setParameter('id', $criteria['id']);
        }

        // Filtrer par prix si le critère est spécifié
        if (isset ($criteria['prix']) && $criteria['prix'] !== null) {
            $queryBuilder
                ->andWhere('p.prix <= :prix')
                ->setParameter('prix', $criteria['prix']);
        }

        // Filtrer par date si le critère est spécifié
        if (isset ($criteria['date']) && $criteria['date'] !== null) {
            // la date soit au format 'YYYY-MM-DD'
            $queryBuilder
                ->andWhere('p.date = :date')
                ->setParameter('date', $criteria['date']);
        }

        // Filtrer par auteur si le critère est spécifié
        if (isset ($criteria['auteur']) && $criteria['auteur'] !== null) {
            $queryBuilder
                ->andWhere('p.auteur = :auteur')
                ->setParameter('auteur', $criteria['auteur']);
        }

        // Ajoutez d'autres conditions pour les autres critères de filtrage (catégorie, etc.)

        // Exécutez la requête et retournez les résultats
        return $queryBuilder->getQuery()->getResult();
    }
}

