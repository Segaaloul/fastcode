<?php

namespace App\Form;

use App\Entity\Solution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class SolutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('framework')
            ->add('domaine')
            ->add('imagePath', FileType::class, [
                'label' => 'Image (JPG/PNG)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('zipFilePath', FileType::class, [
                'label' => 'Fichier ZIP de la solution',
                'required' => true,
                'mapped' => false, // important ! car ce n’est pas un champ directement bindé au champ de l'entité
            ])
            ->add('prix')
            ->add('produitType')
            // ->add('dateAjout', null, [
            //     'widget' => 'single_text',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Solution::class,
        ]);
    }
}
