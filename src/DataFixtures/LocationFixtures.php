<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //$locations = [];

        /*
         * START OF LOCATION LIST
         */
        $temp = new Location();
        $temp->setName("petit 5 guys d'atlantis :)")
            ->setRoadname("Boulevard Salvador Allende")
            ->setZipcode("44880")
            ->setTownname("Saint-Herblain")
            ->setLongitude(-1.630348103605038)
            ->setLatitude(47.22573366643474)
            ->setExtraInfo("En haut sous la coupole")
        ;
        $manager->persist($temp);
        $this->addReference('location_1', $temp);

        $temp = new Location();
        $temp->setName("ptit KFC bien sympa")
            ->setRoadnumber(6)
            ->setRoadname("Rue des Cochardières")
            ->setZipcode("44880")
            ->setTownname("Saint-Herblain")
            ->setLongitude(-1.632892820404875)
            ->setLatitude(47.22811622003771)
        ;
        $manager->persist($temp);
        $this->addReference('location_2', $temp);

        $temp = new Location();
        $temp->setName("Champ de Mars - carrés de pelouse")
            ->setZipcode("75007")
            ->setTownname("Paris")
            ->setLongitude(2.2983698762607476)
            ->setLatitude(48.85579848697538)
            ->setExtraInfo("Un des 3 carrés de pelouse au sud-est")
        ;
        $manager->persist($temp);
        $this->addReference('location_3', $temp);
        /*
         * END OF LOCATION LIST
         */

        $manager->flush();
    }
}
