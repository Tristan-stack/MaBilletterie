<?php

// src/Form/ProduitFilterType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Entity\Produit;


class ProduitFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('id', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'id',
                'required' => false,
                'label' => 'ID'
            ])
            ->add('prix', IntegerType::class, [
                'required' => false,
                'label' => 'Prix maximum'
            ])
            ->add('date', TextType::class, [
                'required' => false,
                'label' => 'Date'
            ])
            ->add('auteur', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'auteur', // ou tout autre champ approprié de l'auteur
                'required' => false,
                'label' => 'Auteur'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }
}


