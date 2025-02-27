<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'label'=> 'Campus *',
                'class' => Campus::class,
                //use the User.campus property as the visible option string
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => '--Renseignez votre campus--',

            ])
            ->add('username', TextType::class, [
                'label' => 'Pseudo'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom *',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prenom *'
            ])
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe *',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Merci d\'entrer un mot de passe',
                        ]),
   //TODO: décommenter la contrainte du password à la fin du projet
   //                     new PasswordStrength([
   //                         'message' => 'Please enter a password more strong',
   //                         'minScore' => PasswordStrength::STRENGTH_WEAK,
   //                     ])
                    ],
                ],
                'second_options' => ['label' => 'Repeat Password *'],
                'mapped' => false,
                'invalid_message' => 'Les mots de passe ne correspondent pas',
            ])

            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone *'
            ])
            ->add('img', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, JPEG,  PNG ou WEBP)',
                    ])
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
