<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;


class AdminAddEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organizer', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getUsername() . ' (' . $user->getEmail() . ')';
                },
                'choice_attr' => function (User $user) {
                    return ['data-campus' => $user->getCampus() ? $user->getCampus()->getName() : 'Aucun campus'];
                },
                'placeholder' => 'Sélectionner un organisateur',
            ])
            ->add('campus', TextType::class, [
                'mapped' => false,
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Campus de l\'organisateur'
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Titre'],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label'=> 'Date et heure de fin',
                'widget' => 'single_text'
            ])
            ->add('endsAt', DateTimeType::class, [
                'label'=> 'Date et heure de fin',
                'widget' => 'single_text'
            ])
            ->add('nbMaxParticipants', IntegerType::class, [
                'label'=> 'Nombre de participants',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Nombre de participants'],
            ])
            ->add('img', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'attr' => ['class'=> 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WEBP)',
                    ])
                    ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Description'],
            ])
            ->add('categories',  EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => true,
                'multiple' => true, // multi select
                'expanded' => true, // checkboxes
                'placeholder' => '--Choisir une catégorie--'])
            ->add('status',  EntityType::class, [
                'label' => 'Statut',
                'class' => Status::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => '--Choisir un statut--'])
            ->add('location',  EntityType::class, [
                'label' => 'Lieu',
                'class' => Location::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => '--Choisir un lieu--'])
                ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'event_class' => Event::class,
        ]);
    }
}
