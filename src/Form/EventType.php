<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use App\Repository\StatusRepository;
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


class EventType extends AbstractType
{


    private StatusRepository $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

//        $isUpdate = $options['is_update']; // Vérifie si c'est une mise à jour
//
//        // Si ce n'est pas une mise à jour, on filtre les statuts pour n'afficher que "Prévu" et "Brouillon"
//        if ($isUpdate) {
//            $allowedStatuses = ['Brouillon', 'Prévu', 'Annulé', 'Archivé'];
//        } else {
//            $allowedStatuses = ['Prévu', 'Brouillon'];
//        }
        $allowedStatuses = ['Prévu', 'Brouillon'];

        $statuses = $this->statusRepository->createQueryBuilder('s')
            ->where('s.name IN (:allowedStatuses)')
            ->setParameter('allowedStatuses', $allowedStatuses)
            ->getQuery()
            ->getResult();




        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class'=> 'form-control', 'placeholder' => 'Titre'],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label'=> 'Date et heure de début',
                'widget' => 'single_text'
            ])
            ->add('endsAt', DateTimeType::class, [
                'label'=> 'Date et heure de fin',
                'widget' => 'single_text'
            ])
            ->add('openUntil', DateTimeType::class, [
                'label'=> 'Date et heure maximale d\'inscription',
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
                        'mimeTypes' => ['image/jpeg','image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, JPEG, PNG, WEBP)',
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
                'choices' => $statuses,
                'required' => true,
                'placeholder' => '--Choisir un statut--'])
            ->add('location',  EntityType::class, [
                'label' => 'Lieu',
                'class' => Location::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => '--Choisir un lieu--'])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-success'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'event_class' => Event::class,
            'is_update' => false,
        ]);
    }
}
