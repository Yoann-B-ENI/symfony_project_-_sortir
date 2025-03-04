<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $statuses = ['Brouillon', 'Prévu', 'En cours', 'Passé', 'Archivé', 'Annulé'];
        foreach ($statuses as $s) {
            $status = new Status();
            $status->setName($s);

            $manager->persist($status);
            $this->addReference("status_" . $s, $status);
        }

        $manager->flush();
    }
}
