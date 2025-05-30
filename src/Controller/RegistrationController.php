<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Security\EmailVerifier;
use App\Service\ImageManagement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    /** Inscription d'un utilisateur et envoi d"un mail de vérification
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager,
                             ImageManagement $imageManagement,
                             #[Autowire('%user_photo_dir%')] string $photoDir,
                             #[Autowire('%user_photo_def_filename%')] string $filename,
    ): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@sortir.com', 'admin sortir'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
         
            $imageFile = $form->get('img')->getData();
            if ($imageFile) {
                $imagePath = $imageManagement->upload($imageFile, $photoDir, $user->getId(), $filename);
                $user->setImg($imagePath);
                $entityManager->flush();
            }
           return $this-> redirectToRoute('app_standBy');

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**Vérification de l'email de l'utilisateur lors de l'inscritpion
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {

        try {
            $id = $request->query->get('id');

            if (!$id) {
                $user = $this->getUser();

                if (!$user) {
                    $this->addFlash('verify_email_error', 'Impossible de vérifier votre email. Veuillez vous connecter ou réessayer.');
                    return $this->redirectToRoute('app_login');
                }
            } else {
                $user = $em->getRepository(User::class)->find($id);

                if (!$user) {
                    $this->addFlash('verify_email_error', 'Utilisateur non trouvé.');
                    return $this->redirectToRoute('app_register');
                }
            }
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            $this->addFlash('success', 'Votre adresse email a été vérifiée.');

            return $this->redirectToRoute('app_login');

        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

    }
    #[Route('/standBy', name: 'app_standBy')]
    public function redirectStandBy(){
        return $this->render('registration/standByRegistration.html.twig');
    }
}
