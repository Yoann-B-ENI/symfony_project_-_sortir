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
        $temp->setName("Five Guys d'Atlantis :)")
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
        $temp->setName("KFC d'Atlantis bien sympa")
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

        $temp = new Location();
        $temp->setName("Jack's Corner")
            ->setRoadnumber(2)
            ->setRoadname("Rue de la Baclerie")
            ->setZipcode("44000")
            ->setTownname("Nantes")
            ->setLongitude(-1.5531639766224583)
            ->setLatitude(47.215180387139704)
        ;
        $manager->persist($temp);
        $this->addReference('location_4', $temp);

        $temp = new Location();
        $temp->setName("Château des ducs de Bretagne - expo")
            ->setRoadnumber(4)
            ->setRoadname("Place Marc Elder")
            ->setZipcode("44000")
            ->setTownname("Nantes")
            ->setLongitude(-1.5489023392602903)
            ->setLatitude(47.21664770980089)
            ->setExtraInfo("Bâtiment de gauche")
        ;
        $manager->persist($temp);
        $this->addReference('location_5', $temp);

        $temp = new Location();
        $temp->setName("Pathé Atlantis")
            ->setRoadnumber(8)
            ->setRoadname("Allée la Pérouse")
            ->setZipcode("44800")
            ->setTownname("Saint-Herblain")
            ->setLongitude(-1.6287900320832804)
            ->setLatitude(47.22284871484565)
        ;
        $manager->persist($temp);
        $this->addReference('location_6', $temp);


        $temp = new Location();
        $temp->setName("Parc de Procé - Manoir")
            ->setRoadnumber(44)
            ->setRoadname("Rue des Dervallières")
            ->setZipcode("44000")
            ->setTownname("Nantes")
            ->setLongitude(-1.582131414650181)
            ->setLatitude(47.22413881986674)
            ->setExtraInfo("suivre indications manoir une fois dedans")
        ;
        $manager->persist($temp);
        $this->addReference('location_7', $temp);
        /*
         * END OF LOCATION LIST
         */

        $manager->flush();
    }
}
