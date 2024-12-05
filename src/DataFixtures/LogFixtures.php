<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Log;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * Class LogFixtures
 *
 * The testing log data fixtures
 *
 * @package App\DataFixtures
 */
class LogFixtures extends Fixture
{
    /**
     * Load log fixtures
     *
     * @param ObjectManager $manager The entity manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            // create log entity
            $log = new Log();
            $log->setName('log-fixture')
                ->setMessage($faker->sentence(10))
                ->setTime($faker->dateTimeBetween('-1 year', 'now'))
                ->setLevel($faker->numberBetween(1, 4))
                ->setUserId($faker->numberBetween(1, 100))
                ->setUserAgent('data-fixture-user-agent')
                ->setRequestUri('https://api.becvar.xyz/api/test')
                ->setRequestMethod($faker->randomElement(['GET', 'POST', 'PUT', 'DELETE']))
                ->setIpAddress('127.0.0.1')
                ->setStatus('open');

            // persist log entity
            $manager->persist($log);
        }

        // flush log entities to database
        $manager->flush();
    }
}