<?php

namespace App\Tests\Controller\Admin;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserListControllerTest
 *
 * Test cases for user list API endpoint
 *
 * @package App\Tests\Controller\Admin
 */
class UserListControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get users list when request method is invalid
     *
     * @return void
     */
    public function testGetUsersListWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/user/list');

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get users list when auth token is not provided
     *
     * @return void
     */
    public function testGetUsersListWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/admin/user/list');

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get users list when auth token is invalid
     *
     * @return void
     */
    public function testGetUsersListWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/user/list', [], [], ['HTTP_AUTHORIZATION' => 'Bearer invalid-token']);

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get users list success
     *
     * @return void
     */
    public function testGetUsersListSuccess(): void
    {
        $this->client->request('GET', '/api/admin/user/list', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        /** @var array<mixed> $user */
        $user = $responseData['users'][0];

        // assert response
        $this->assertNotEmpty($responseContent);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('users', $responseData);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('first-name', $user);
        $this->assertArrayHasKey('last-name', $user);
        $this->assertArrayHasKey('roles', $user);
        $this->assertArrayHasKey('register-time', $user);
        $this->assertArrayHasKey('last-login-time', $user);
        $this->assertArrayHasKey('ip-address', $user);
        $this->assertArrayHasKey('browser', $user);
        $this->assertArrayHasKey('status', $user);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
