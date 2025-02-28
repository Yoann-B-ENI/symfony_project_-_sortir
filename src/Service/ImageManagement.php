<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManagement{

    // src/Service/ImageManagement.php

    public function upload(UploadedFile $imageFile, string $baseDirectory, int $entityId, string $defaultFilename): string
    {

        $targetDirectory = $baseDirectory . '/' . $entityId;

        $filename = $defaultFilename . '.' . $imageFile->guessExtension();

        $imageFile->move($targetDirectory, $filename);

        return $filename;
    }

    public function updateImage(?string $currentImage, UploadedFile $newImage, string $baseDirectory, int $entityId, string $defaultFilename): string
    {
        $targetDirectory = $baseDirectory . '/' . $entityId;
        $newFilename = $defaultFilename . '.' . $newImage->guessExtension();
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        // ✅ Supprimer l'ancienne image si elle existe
        if ($currentImage && $filesystem->exists($targetDirectory . '/' . $currentImage)) {
            $filesystem->remove($targetDirectory . '/' . $currentImage);
        }

        // ✅ Réutiliser la fonction `upload()`
        return $this->upload($newImage, $baseDirectory, $entityId, $defaultFilename);
    }

    public function deleteImage(string $baseDirectory, int $entityId): void
    {
        $targetDirectory = $baseDirectory . '/' . $entityId;
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        // ✅ Vérifier si le dossier existe avant de le supprimer
        if ($filesystem->exists($targetDirectory)) {
            $filesystem->remove($targetDirectory);
        }


    }


// CREATE ANCIEN CODE
    // $imageFile = $form->get('img')->getData();

    /*if ($imageFile) {
        // cover_img -> cover_img.jpg/png/...
        $filename = $filename . '.' . $imageFile->guessExtension();
        $event->setImg($filename);
    } */

    /* if ($imageFile) {
                    // events/images -> events/images/5
                    $photoDir = $photoDir . "/" . $event->getId();
                    $imageFile->move($photoDir, $filename);
                }*/

//UPDATE ANCIEN CODE



// DELETE ANCIEN CODE :

    //$photoDir = $this->getParameter('kernel.project_dir') . '/public/uploads/events/' . $event->getId();
    //$filesystem->remove($photoDir);
    // $filesystem = new Filesystem();



}