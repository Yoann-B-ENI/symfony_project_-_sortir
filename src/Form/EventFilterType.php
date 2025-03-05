<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\Status;
use App\Entity\User;
use App\Repository\StatusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFilterType extends AbstractType
{
    private StatusRepository $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $statuses = $this->statusRepository->createQueryBuilder('s')
            ->where('s.name != :archived')
            ->setParameter('archived', 'Archivé')
            ->getQuery()
            ->getResult();

        $builder
            ->add('organizer', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'placeholder' => 'Tous les utilisateurs',
                'required' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder'=> 'Tous les campus',
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder'=> 'Toutes les categories',
                'required' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'name',
                'choices' => $statuses,
                'choice_value' => 'id',
                'placeholder'=> 'Tous les statuts',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}