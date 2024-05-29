<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Link;
use App\Entity\Song;
use Faker\Generator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{

    private Generator $faker;

    public function __construct(){
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
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
