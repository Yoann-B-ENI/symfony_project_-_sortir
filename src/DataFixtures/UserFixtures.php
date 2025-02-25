<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    private UserPasswordHasherInterface $pwdHasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->pwdHasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
//        $temp = new User();
//        $temp->setEmail('%%')
//            ->setFirstName('%%')
//            ->setLastName('%%')
//            ->setPassword($this->pwdHasher->hashPassword($temp, '%%'))
//            ->setTelephone('%%')
//            ->setUsername('%%')
//            ->setCampus($this->getReference('campus_Nantes', Campus::class))
//            ->setRoles(['ROLE_USER']);
//        ;
//        $manager->persist($temp);
//        $this->addReference('user_' . $temp->getUsername(), $temp);

        /*
         * START OF USER LIST
         */
        $temp = new User();
        $temp->setEmail('jean.dupont@example.fr')
            ->setFirstName('Jean')
            ->setLastName('Dupont')
            ->setPassword($this->pwdHasher->hashPassword($temp, '1234'))
            ->setTelephone('0601020304')
            ->setUsername('jean.dupont')
            ->setCampus($this->getReference('campus_Nantes', Campus::class))
            ->setRoles(['ROLE_USER']);
        ;
        $manager->persist($temp);
        $this->addReference('user_' . $temp->getUsername(), $temp);

        $temp = new User();
        $temp->setEmail('marie.lemoine@example.fr')
            ->setFirstName('Marie')
            ->setLastName('Lemoine')
            ->setPassword($this->pwdHasher->hashPassword($temp, '1234'))
            ->setTelephone('0612345678')
            ->setUsername('Sleepy_Panda')
            ->setCampus($this->getReference('campus_Nantes', Campus::class))
            ->setRoles(['ROLE_USER']);
        ;
        $manager->persist($temp);
        $this->addReference('user_' . $temp->getUsername(), $temp);

        $temp = new User();
        $temp->setEmail('lucas.martin@example.fr')
            ->setFirstName('Lucas')
            ->setLastName('Martin')
            ->setPassword($this->pwdHasher->hashPassword($temp, '1234'))
            ->setTelephone('0623456789')
            ->setUsername('Lucas 44')
            ->setCampus($this->getReference('campus_Niort', Campus::class))
            ->setRoles(['ROLE_USER']);
        ;
        $manager->persist($temp);
        $this->addReference('user_' . $temp->getUsername(), $temp);

        $temp = new User();
        $temp->setEmail('sophie.durand@example.fr')
            ->setFirstName('Sophie')
            ->setLastName('Durand')
            ->setPassword($this->pwdHasher->hashPassword($temp, '1234'))
            ->setTelephone('0634567890')
            ->setUsername('Sophie D.')
            ->setCampus($this->getReference('campus_Nantes', Campus::class))
            ->setRoles(['ROLE_USER']);
        ;
        $manager->persist($temp);
        $this->addReference('user_' . $temp->getUsername(), $temp);

        $temp = new User();
        $temp->setEmail('nicolas.perez@example.fr')
            ->setFirstName('Nicolas')
            ->setLastName('Perez')
            ->setPassword($this->pwdHasher->hashPassword($temp, 'adminadmin'))
            ->setTelephone('0645678901')
            ->setUsername('nico super admin')
            ->setCampus($this->getReference('campus_Nantes', Campus::class))
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        ;
        $manager->persist($temp);
        $this->addReference('user_' . $temp->getUsername(), $temp);

        $temp = new User();
        $temp->setEmail('amelie.caillet@example.fr')
            ->setFirstName('Amélie')
            ->setLastName('Caillet')
            ->setPassword($this->pwdHasher->hashPassword($temp, 'adminadmin'))
            ->setTelephone('0645678901')
            ->setUsername('Amélie C')
            ->setCampus($this->getReference('campus_Nantes', Campus::class))
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        ;
        $manager->persist($temp);
        $this->addReference('user_' . $temp->getUsername(), $temp);
        /*
         * END OF USER LIST
         */

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CampusFixtures::class];
    }
}
