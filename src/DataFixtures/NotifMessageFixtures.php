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
        $temp->setMessage("Message pour tout le monde!")
            ->setIsFlagged(false)
            ->setRoles("['ROLE_USER']")
            ->setCreatedAt(new \DateTimeImmutable())
        ;
        $manager->persist($temp);

        $temp = new NotifMessage();
        $temp->setMessage("Message imoprtant pour tous les admins")
            ->setIsFlagged(true)
            ->setRoles("['ROLE_ADMIN']")
            ->setCreatedAt(new \DateTimeImmutable())
        ;
        $manager->persist($temp);

        $temp = new NotifMessage();
        $temp->setMessage("Message d'administratif perso juste pour Amélie")
            ->setIsFlagged(true)
            ->setRoles("['ROLE_ADMIN']")
            ->setCreatedAt(new \DateTimeImmutable())
        ;
        $manager->persist($temp);
        $this->addReference("notif_test_amélie", $temp);

        $manager->flush();
    }
}
