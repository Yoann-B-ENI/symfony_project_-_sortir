<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = ['Voyage', 'Sport', 'Jeux', 'Repas', 'Boisson', 'Education', 'Danse', 'Artisanat', 'Photo', 'Cinéma'];
        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat);

            $manager->persist($category);
            $this->addReference("category_{$cat}", $category);
        }

        $manager->flush();
    }
}
