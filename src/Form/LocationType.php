<?php

namespace App\Form;

use App\Entity\Location;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom lieu *',
                'required' => true,
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Nom lieu'],
            ])
            ->add('roadnumber', TextType::class, [
                'label' => 'Numéro de rue',
                'required' => false,
                'attr' => ['class'=> 'form-control', 'placeholder' => '123'],
            ])
            ->add('roadname', TextType::class, [
                'label' => 'Nom de la rue',
                'required' => false,
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Rue de ...'],
            ])
            ->add('zipcode', TextType::class, [
                'label' => 'Code postal *',
                'required' => true,
                'attr' => ['class'=> 'form-control', 'placeholder' => '00000'],
            ])
            ->add('townname', TextType::class, [
                'label' => 'Nom de la ville *',
                'required' => true,
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Ville'],
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude *',
                'required' => true,
                'attr' => ['class'=> 'form-control', 'placeholder' => '45.1234'],
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude *',
                'required' => true,
                'attr' => ['class'=> 'form-control', 'placeholder' => '1.1234'],
            ])
            ->add('extraInfo', TextareaType::class, [
                'label' => 'Détails du lieu',
                'required' => false,
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Informations supplémentaires sur le lieu (numéro de salle, ...)'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
