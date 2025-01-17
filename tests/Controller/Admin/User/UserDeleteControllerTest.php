<?php

namespace App\Tests\Controller\Admin\User;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserDeleteControllerTest
 *
 * Test cases for delete user api endpoint
 *
 * @package App\Tests\Controller\Admin\User
 */
class UserDeleteControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test delete user when request method is not valid
     *
     * @return void
     */
    public function testDeleteUserWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/user/delete');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test delete user when auth token is not provided
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete user when api access token is not provided
     *
     * @return void
     */
    public function testDeleteUserWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete user when auth token is invalid
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete user when request data is not provided
     *
     * @return void
     */
    public function testDeleteUserWhenRequestDataIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Parameter "user_id" are required!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete user when user id is empty
     *
     * @return void
     */
    public function testDeleteUserWhenUserIdIsEmpty(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete', [
            'user_id' => ''
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Parameter "user_id" are required!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete user when user id not exist
     *
     * @return void
     */
    public function testDeleteUserWhenUserIdNotExist(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete', [
            'user_id' => 999999999
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('User id: 999999999 not found in database!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test delete user successful
     *
     * @return void
     */
    public function testDeleteUserSuccessful(): void
    {
        $this->client->request('DELETE', '/api/admin/user/delete', [
            'user_id' => 6
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('User deleted successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
