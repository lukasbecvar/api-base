<?php

namespace App\Tests\Controller;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class IndexControllerTest
 *
 * Test cases for index controller
 *
 * @package App\Tests\Controller
 */
class IndexControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test load index route
     *
     * @return void
     */
    public function testLoadIndexRoute(): void
    {
        $this->client->request('GET', '/');

        /** @var array<string> $response */
        $response = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('success', $response['status']);
        $this->assertSame('api-base is running!', $response['message']);
        $this->assertSame($_ENV['APP_VERSION'], $response['version']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
