<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\Censuror;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class EventController extends AbstractController
{


    #[Route('/event/', name: 'event')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $eventsList = $entityManager->getRepository(Event::class)->findAll();
        return $this->render('event/index.html.twig', [

            'eventsList' => $eventsList,
        ]);
    }

    #[Route('/event/create', name: 'event_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, Censuror $censuror, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setDescription($censuror->purify($event->getDescription()));

            $entityManager->persist($event);
            $entityManager->flush();

            if (!$event->getId()) {
                throw new \Exception("Erreur : l'événement n'a pas pu être créé.");
            }

            if ($imageFile = $form->get('img')->getData()) {

                $filename = bin2hex(random_bytes(6)) . '.' . $imageFile->guessExtension();

                $imageFile->move($photoDir, $filename);


                $event->setImg($filename);
                $entityManager->flush();
            }

            return $this->redirectToRoute('event');
        }




        return $this->render('event/create.html.twig', [
            'form'=>$form,

        ]);
    }


    #[Route('/event/{id}/update', name: 'event_update')]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        Event $event,
        #[Autowire('%photo_dir%')] string $photoDir // 🔹 Injection du répertoire d'upload
    ): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 🔹 Vérifier si une nouvelle image est téléchargée
            if ($imageFile = $form->get('img')->getData()) {
                // 🔹 Supprimer l'ancienne image si elle existe
                if ($event->getImg()) {
                    $oldImagePath = $photoDir . '/' . $event->getImg();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // 🔹 Générer un nom unique pour l'image
                $filename = bin2hex(random_bytes(6)) . '.' . $imageFile->guessExtension();

                // 🔹 Déplacer l'image dans le bon dossier
                $imageFile->move($photoDir, $filename);

                // 🔹 Associer la nouvelle image à l'événement
                $event->setImg($filename);
            }

            // 🔹 Sauvegarder les changements
            $entityManager->flush();
            return $this->redirectToRoute('event');
        }

        return $this->render('event/update.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }


    #[Route('/event/{id}/delete', name: 'event_delete')]
public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

    return $this->redirectToRoute('event');
    }

    #[Route('/event/{id}', name: 'event_details', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        return $this->render('event/details.html.twig', [
            'event' => $event,
        ]);
    }


}
