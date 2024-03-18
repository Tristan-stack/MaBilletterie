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
use App\Entity\Produit;

class ProduitFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('auteur', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'auteur', 
                'required' => false,
            ])
            ->add('prix', ChoiceType::class, [
                'choices' => [
                    'Moins de 50' => '0-50',
                    '50 à 100' => '50-100',
                    'Plus de 100' => '100+',
                ],
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('filter', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }
}