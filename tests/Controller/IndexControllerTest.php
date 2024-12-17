<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class IndexControllerTest
 *
 * Test cases for index controller
 *
 * @package App\Tests\Controller
 */
class IndexControllerTest extends WebTestCase
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

        // get response data
        $response = json_decode((string) ($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $response['status']);
        $this->assertSame('api-base is running!', $response['message']);
        $this->assertSame($_ENV['APP_VERSION'], $response['version']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
