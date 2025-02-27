<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $temp = new Event();
        $temp->setTitle('Petite bouffe de fin de projet')
            ->setDescription('Un petit resto sympa pour féter tout ça')
            ->setStartsAt(new \DateTimeImmutable('07-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('07-03-2025 22:30'))
            ->setNbMaxParticipants(20)
            ->setStatus($this->getReference("status_Prévu", Status::class))
            ->setOrganizer($this->getReference("user_jean.dupont", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_1", Location::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('Atelier poterie')
            ->setDescription('Petit atelier poterie et voici des gros mots pour tester la censure : merde pute sacré de sacré un concept ')
            ->setStartsAt(new \DateTimeImmutable('07-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('09-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('08-03-2025 22:30'))
            ->setNbMaxParticipants(5)
            ->setStatus($this->getReference("status_Prévu", Status::class))
            ->setOrganizer($this->getReference("user_nico super admin", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_2", Location::class))
            ->addCategory($this->getReference("category_Education", Category::class))
            ->addCategory($this->getReference("category_Artisanat", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
        ;
        $manager->persist($temp);


        $temp = new Event();
        $temp->setTitle('Atelier photo')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('23-02-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('23-02-2025 22:30'))
            ->setNbMaxParticipants(5)
            ->setStatus($this->getReference("status_Passé", Status::class))
            ->setOrganizer($this->getReference("user_Lucas 44", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_3", Location::class))
            ->addCategory($this->getReference("category_Photo", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Lucas 44", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('Atelier ouvert mais inscription passé')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('10-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('11-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('20-02-2025 22:30'))
            ->setNbMaxParticipants(5)
            ->setStatus($this->getReference("status_Passé", Status::class))
            ->setOrganizer($this->getReference("user_Lucas 44", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_3", Location::class))
            ->addCategory($this->getReference("category_Photo", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Lucas 44", User::class))
        ;
        $manager->persist($temp);



        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [StatusFixtures::class,
            UserFixtures::class,
            LocationFixtures::class,
            CategoryFixtures::class,
            ];
    }
}
