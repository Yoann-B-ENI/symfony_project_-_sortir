<?php

namespace App\DataFixtures;

use App\Entity\NotifMessage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NotifMessageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $temp = new NotifMessage();
        $temp->setMessage("Ceci est un message de test pour tout le monde")
            ->setIsFlagged(true)
            ->setRoles("['ROLE_USER']")
            ;
        $manager->persist($temp);

        $temp = new NotifMessage();
        $temp->setMessage("Ceci est un autre message de test pour tout le monde mais déja lu")
            ->setIsFlagged(false)
            ->setRoles("['ROLE_USER']")
        ;
        $manager->persist($temp);

        $temp = new NotifMessage();
        $temp->setMessage("Ceci est un message de test pour tout les admins")
            ->setIsFlagged(true)
            ->setRoles("['ROLE_ADMIN']")
        ;
        $manager->persist($temp);

        $temp = new NotifMessage();
        $temp->setMessage("Ceci est un message de test pour Améliiiiiiiie")
            ->setIsFlagged(true)
            ->setRoles("['ROLE_ADMIN']")
        ;
        $manager->persist($temp);
        $this->addReference("notif_test_amélie", $temp);

        $manager->flush();
    }
}
