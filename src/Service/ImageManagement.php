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





}