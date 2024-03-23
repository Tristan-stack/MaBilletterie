<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Produit;
use App\Repository\ProduitRepository;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    #[Route('/admin', name: 'admin_users')]
    public function listUsers(UserRepository $userRepository, ProduitRepository $produitRepository): Response
    {
        $users = $userRepository->findAll();
        $produits = $produitRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'produits' => $produits,
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/admin/product/delete/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function deleteProduct(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            // Supprime le fichier associé à l'entité
            $imagePath = $this->getParameter('images_directory') . '/' . $produit->getImgProduit();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}