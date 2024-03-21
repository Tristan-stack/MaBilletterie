<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Doctrine\ORM\EntityManagerInterface;

class LogoutSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutSuccess',
        ];
    }

    public function onLogoutSuccess(LogoutEvent $event)
    {
        // Récupérez l'utilisateur connecté
        $user = $event->getToken()->getUser();

        // Vérifiez si l'utilisateur est connecté
        if ($user) {
            // Récupérez le panier de l'utilisateur
            $panier = $user->getPanier();

            // Supprimez tous les produits du panier
            foreach ($panier->getProduits() as $produit) {
                $panier->removeProduit($produit);
            }

            // Mettez à jour le panier dans la base de données
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        }
    }
}