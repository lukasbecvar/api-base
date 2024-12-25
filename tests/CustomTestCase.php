<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Class CustomTestCase
 *
 * Custom test case for testing controllers
 *
 * @package App\Tests
 */
class CustomTestCase extends WebTestCase
{
    /**
     * Generate user token for testing purposes
     *
     * @return string The generated JWT token
     */
    public function generateJwtToken(): string
    {
        // init test user (created with datafixtures)
        $fakeUser = new User();
        $fakeUser->setEmail('test@test.test');

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);

        // generate JWT token
        return $jwtManager->create($fakeUser);
    }
}
