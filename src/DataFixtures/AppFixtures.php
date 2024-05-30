<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Link;
use App\Entity\Song;
use App\Entity\User;
use Faker\Generator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private Generator $faker;

    private UserPasswordHasherInterface $userPasswordHasher; 
    public function __construct(UserPasswordHasherInterface $userPasswordHasher){
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $password = $this->faker->password(6,10);
        $adminUser = new User();
        $adminUser->setUuid($this->faker->uuid())->setUsername($adminUser->getUuid() . "@" . $password)
        ->setRoles(["ROLE_ADMIN"])->setPassword($this->userPasswordHasher->hashPassword($adminUser, "password") );
$manager->persist($adminUser);
        for($i = 0; $i < 10; $i++){
                $password = $this->faker->password(6,10);
        $userUser = new User();
        $userUser->setUuid($this->faker->uuid())->setUsername($userUser->getUuid() . "@" . $password)
        ->setRoles(["ROLE_USER"])->setPassword($this->userPasswordHasher->hashPassword($userUser,$password) );
            $manager->persist($userUser);
    }


        $songs = [];
        for($i = 0; $i < 20; $i++){
            $created = $this->faker->dateTime();
            $updated = $this->faker->dateTimeBetween($created, "now");

            $song = new Song();
            $song->setName($this->faker->word())
                ->setStatus("on")
                ->setCreatedAt($created)
                ->setUpdatedAt($updated);
            $songs[] = $song;       
            $manager->persist($song);
        }
        $manager->flush();


             for($i = 0; $i < 20; $i++){
            $created = $this->faker->dateTime();
            $updated = $this->faker->dateTimeBetween($created, "now");

            $link = new Link();
            $link->setUrl($this->faker->url())
                ->setStatus("on")
                ->setCreatedAt($created)
                ->setUpdatedAt($updated)
                ->addSong($songs[array_rand($songs)]);
            $manager->persist($link);
        }
        $manager->flush();
    }
}
