<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\ProduitFilterType;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitFilterType::class);
        $form->handleRequest($request);
        //dump($form->isSubmitted());
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $produits = $produitRepository->filter($data);
            

            //return $this->redirectToRoute('app_produit_index');
            return new Response();
        } else {
            $produits = $produitRepository->findAll();
        }

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser(); // récupère l'utilisateur actuellement connecté
            if (!$user) {
                throw new \Exception('Vous devez être connecté pour créer un produit');
            }

            $produit->setUser($user); // associe l'utilisateur au produit

            // Augmenter le nb_produit de la catégorie
            $category = $produit->getCategory();
            $category->setNbProduit($category->getNbProduit() + 1);

            /** @var UploadedFile $imageFile */
            $imageFile = $form['image']->getData();

            // cette condition est nécessaire car le champ 'image' n'est pas obligatoire
            // ducoup, le fichier doit être traité que lorsqu'un fichier est téléchargé
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // pour inclure en toute sécurité le nom de fichier comme partie de l'URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // deplace le fichier dans le dossier où sont stockées les images
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // au caou
                    throw new \Exception('Une erreur est survenue lors du téléchargement du fichier');
                }
                $produit->setImgProduit($newFilename);
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser(); // récupère l'utilisateur actuellement connecté
        if (!$user || $user->getId() !== $produit->getUser()->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas éditer ce produit');
        }

        $oldImage = $produit->getImgProduit();

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form['image']->getData();

            // Si une nouvelle image a été téléchargée
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // deplace le fichier dans le dossier où sont stockées les images
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Une erreur est survenue lors du téléchargement du fichier');
                }

                // Supprime l'ancienne image
                $oldImagePath = $this->getParameter('images_directory') . '/' . $oldImage;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }

                $produit->setImgProduit($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
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
