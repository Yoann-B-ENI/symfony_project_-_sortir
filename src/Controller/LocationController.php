<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LocationController extends AbstractController
{
    private GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    #[Route('/location', name: 'app_location')]
    public function index(LocationRepository $locationRepo,
                          Request $request, EntityManagerInterface $em): Response
    {
        $locations = $locationRepo->findAll();
        $location = new Location();
        $locForm = $this->createForm(LocationType::class, $location);

        $locForm->handleRequest($request);
        if ($locForm->isSubmitted() && $locForm->isValid()){
            // Try to geocode the address
            $geocodedCoords = $this->geocodingService->geocodeAddress(
                $location->getRoadnumber() ?? '',
                $location->getRoadname() ?? '',
                $location->getZipcode(),
                $location->getTownname()
            );

            // If geocoding is successful, set latitude and longitude
            if ($geocodedCoords) {
                $location->setLatitude($geocodedCoords['latitude']);
                $location->setLongitude($geocodedCoords['longitude']);
            } else {
                // Optional: Add a flash message about geocoding failure
                $this->addFlash('warning', 'Géolocalisation automatique a échoué. Veuillez vérifier les coordonnées.');
            }

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
    public function update(Location $loc,
                           Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $locForm = $this->createForm(LocationType::class, $loc);

        $locForm->handleRequest($request);
        if ($locForm->isSubmitted()){
            if ($locForm->isValid()){
                // Try to geocode the address during update
                $geocodedCoords = $this->geocodingService->geocodeAddress(
                    $loc->getRoadnumber() ?? '',
                    $loc->getRoadname() ?? '',
                    $loc->getZipcode(),
                    $loc->getTownname()
                );

                // If geocoding is successful, update latitude and longitude
                if ($geocodedCoords) {
                    $loc->setLatitude($geocodedCoords['latitude']);
                    $loc->setLongitude($geocodedCoords['longitude']);
                } else {
                    $this->addFlash('warning', 'Géolocalisation automatique a échoué. Veuillez vérifier les coordonnées.');
                }

                $em->flush();
            }
            return $this->redirectToRoute('app_location');
        }

        return $this->render('location/update.html.twig', [
            'locForm' => $locForm,
            'title' => 'Modification d\'une adresse',
        ]);
    }

    #[Route('location/delete/{id}', name: 'app_location_delete', requirements: ['id' => '\d+'])]
    public function delete(Location $loc, // database call
                           EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($loc);
        $em->flush();

        return $this->redirectToRoute('app_location');
    }
}