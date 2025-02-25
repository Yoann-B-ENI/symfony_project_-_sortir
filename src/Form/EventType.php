<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;


class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Titre'],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label'=> 'Date et heure de début',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Date et heure début'],
            ])
            ->add('endsAt', DateTimeType::class, [
                'label'=> 'Date et heure de fin',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Date et heure de fin'],
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

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'event_class' => Event::class,
        ]);
    }
}
