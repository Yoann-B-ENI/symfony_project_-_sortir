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
        $temp->setTitle('Petit resto')
            ->setDescription('Un petit resto sympa pour féter tout ça')
            ->setStartsAt(new \DateTimeImmutable('07-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('07-03-2025 22:30'))
            ->setNbMaxParticipants(20)
            ->setStatus($this->getReference("status_Prévu", Status::class))
            ->setOrganizer($this->getReference("user_jean.dupont", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_1", Location::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
            ->addParticipant($this->getReference("user_Dev Fanch", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('Atelier poterie')
            ->setDescription('Petit atelier poterie et voici des gros mots pour tester la censure : merde pute sacré de sacré un concept ')
            ->setStartsAt(new \DateTimeImmutable('07-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('09-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('07-03-2025 15:30'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_Prévu", Status::class))
            ->setOrganizer($this->getReference("user_nico super admin", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_2", Location::class))
            ->addCategory($this->getReference("category_Education", Category::class))
            ->addCategory($this->getReference("category_Artisanat", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Danse", Category::class))
            ->addCategory($this->getReference("category_Jeux", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);


        $temp = new Event();
        $temp->setTitle('Atelier photo')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('23-02-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('23-02-2025 22:30'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_Passé", Status::class))
            ->setOrganizer($this->getReference("user_Lucas 44", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_3", Location::class))
            ->addCategory($this->getReference("category_Photo", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Sport", Category::class))
            ->addCategory($this->getReference("category_Voyage", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('Evénement ouvert mais inscription passé')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('10-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('11-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('20-02-2025 22:30'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_Prévu", Status::class))
            ->setOrganizer($this->getReference("user_Lucas 44", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_4", Location::class))
            ->addCategory($this->getReference("category_Photo", Category::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Sport", Category::class))
            ->addCategory($this->getReference("category_Voyage", Category::class))
            ->addCategory($this->getReference("category_Jeux", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('Brouillon de sortie ciné')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('10-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('11-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('20-02-2025 22:30'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_Annulé", Status::class))
            ->setOrganizer($this->getReference("user_Lucas 44", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_6", Location::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addCategory($this->getReference("category_Cinéma", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('sortie annulé')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('10-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('11-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('20-02-2025 22:30'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_Annulé", Status::class))
            ->setOrganizer($this->getReference("user_Amélie C", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_5", Location::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addCategory($this->getReference("category_Cinéma", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('sortie archivé')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('25-03-2025 19:00'))
            ->setEndsAt(new \DateTimeImmutable('27-03-2025 22:30'))
            ->setOpenUntil(new \DateTimeImmutable('20-02-2025 22:30'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_Archivé", Status::class))
            ->setOrganizer($this->getReference("user_Amélie C", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_5", Location::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addCategory($this->getReference("category_Cinéma", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);


        $temp = new Event();
        $temp->setTitle('sortie en cours')
            ->setDescription('hop')
            ->setStartsAt(new \DateTimeImmutable('06-03-2025 15:00'))
            ->setEndsAt(new \DateTimeImmutable('06-03-2025 17:30'))
            ->setOpenUntil(new \DateTimeImmutable('20-02-2025 14:00'))
            ->setNbMaxParticipants(8)
            ->setStatus($this->getReference("status_En cours", Status::class))
            ->setOrganizer($this->getReference("user_Amélie C", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_5", Location::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addCategory($this->getReference("category_Cinéma", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
        ;
        $manager->persist($temp);

        $temp = new Event();
        $temp->setTitle('Anniversaire de François')
            ->setDescription('hop on va souffler sur des bougies :3 hehehe je vais mettre des gros mots dans la page merde con sacré de sacré')
            ->setStartsAt(new \DateTimeImmutable('06-03-2025 17:30'))
            ->setEndsAt(new \DateTimeImmutable('06-03-2025 23:59'))
            ->setOpenUntil(new \DateTimeImmutable('06-03-2025 17:15'))
            ->setNbMaxParticipants(66142961)
            ->setStatus($this->getReference("status_Prévu", Status::class))
            ->setOrganizer($this->getReference("user_Sleepy_Panda", User::class))
            ->setCampus($temp->getOrganizer()->getCampus())
            ->setLocation($this->getReference("location_4", Location::class))
            ->addCategory($this->getReference("category_Boisson", Category::class))
            ->addCategory($this->getReference("category_Repas", Category::class))
            ->addCategory($this->getReference("category_Danse", Category::class))
            ->addCategory($this->getReference("category_Jeux", Category::class))
            ->addParticipant($this->getReference("user_jean.dupont", User::class))
            ->addParticipant($this->getReference("user_Sleepy_Panda", User::class))
            ->addParticipant($this->getReference("user_nico super admin", User::class))
            ->addParticipant($this->getReference("user_Amélie C", User::class))
            ->addParticipant($this->getReference("user_Dark Yoyo", User::class))
            ->addParticipant($this->getReference("user_JuL", User::class))
            ->addParticipant($this->getReference("user_Juju Wat", User::class))
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
