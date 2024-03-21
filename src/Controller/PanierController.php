<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/panier', name: 'app_panier_index')]
    public function index(): Response
    {
        // Récupérez l'utilisateur connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est connecté
        if (!$user) {
            // Redirigez vers la page de connexion si aucun utilisateur n'est connecté
            return $this->redirectToRoute('app_login');
        }

        // Récupérez le panier de l'utilisateur
        $panier = $user->getPanier();

        // Récupérez les produits dans le panier
        $produits = $panier->getProduits();

        // Affichez la vue index du panier avec les produits
        return $this->render('panier/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/panier/add/{id}', name: 'app_panier_add')]
    public function add(int $id, ProduitRepository $produitRepository): Response
    {
        // Récupérez l'utilisateur connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est connecté
        if (!$user) {
            // Redirigez vers la page de connexion si aucun utilisateur n'est connecté
            return $this->redirectToRoute('app_login');
        }

        // Récupérez le produit à ajouter au panier
        $produit = $produitRepository->find($id);

        // Créez un nouveau Panier ou récupérez le panier existant de l'utilisateur
        $panier = $user->getPanier() ?? new Panier();

        // Ajoutez le produit au panier
        $panier->addProduit($produit);

        // Si c'est un nouveau Panier, définissez l'utilisateur
        if (!$user->getPanier()) {
            $panier->setUser($user);
        }

        // Enregistrez le panier dans la base de données
        $this->entityManager->persist($panier);
        $this->entityManager->flush();

        // Redirigez vers la page d'index du panier
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/panier/remove/{id}', name: 'app_panier_remove', methods: ['POST'])]
    public function remove(int $id, ProduitRepository $produitRepository): Response
    {
        // Récupérez l'utilisateur connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est connecté
        if (!$user) {
            // Redirigez vers la page de connexion si aucun utilisateur n'est connecté
            return $this->redirectToRoute('app_login');
        }

        // Récupérez le produit à supprimer
        $produit = $produitRepository->find($id);

        // Récupérez le panier de l'utilisateur
        $panier = $user->getPanier();

        // Supprimez le produit du panier
        $panier->removeProduit($produit);

        // Enregistrez le panier dans la base de données
        $this->entityManager->persist($panier);
        $this->entityManager->flush();

        // Redirigez vers la page d'index du panier
        return $this->redirectToRoute('app_panier_index');
    }
}