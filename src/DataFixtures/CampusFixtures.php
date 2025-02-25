<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campuses = ['Niort', 'Nantes', 'Paris', 'Bordeaux', 'Marseille'];
        foreach ($campuses as $c) {
            $campus = new Campus();
            $campus->setName($c);

            $manager->persist($campus);
            $this->addReference('campus_' . $c, $campus);
        }

        $manager->flush();
    }
}
