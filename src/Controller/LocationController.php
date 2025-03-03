<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LocationController extends AbstractController
{
    #[Route('/location', name: 'app_location')]
    public function index(LocationRepository $locationRepo,
                          Request $request, EntityManagerInterface $em): Response
    {
        $locations = $locationRepo->findAll();
        $location = new Location();
        $locForm = $this->createForm(LocationType::class, $location);

        $locForm->handleRequest($request);
        if ($locForm->isSubmitted() && $locForm->isValid()){
            $em->persist($location);
            $em->flush();
            return $this->redirectToRoute('app_location');
        }

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
            'locForm' => $locForm,
        ]);
    }

    #[Route('location/update/{id}', name: 'app_location_update', requirements: ['id' => '\d+'])]
    public function update(Location $loc, // database call
                           Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $locForm = $this->createForm(LocationType::class, $loc);

        $locForm->handleRequest($request);
        if ($locForm->isSubmitted()){
            if ($locForm->isValid()){
                $em->flush();
            }
            return $this->redirectToRoute('app_location');
        }

        return $this->render('location/update.html.twig',
            ['locForm' => $locForm]);
    }

    #[Route('location/delete/{id}', name: 'app_location_delete', requirements: ['id' => '\d+'])]
    public function delete(Location $loc, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($loc);
        $em->flush();

        return $this->redirectToRoute('app_location');
    }


}
