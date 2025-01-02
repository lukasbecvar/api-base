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

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

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
        $this->client->request('POST', '/api/admin/user/delete');

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete user when auth token is invalid
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token',
        ]);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

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
        $this->client->request('POST', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('Request body is empty.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete user when user id is empty
     *
     * @return void
     */
    public function testDeleteUserWhenUserIdIsEmpty(): void
    {
        $this->client->request('POST', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'user-id' => ''
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('Parameter "status" are required!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete user when user id not exist
     *
     * @return void
     */
    public function testDeleteUserWhenUserIdNotExist(): void
    {
        $this->client->request('POST', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'user-id' => 999999999
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('User not found!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test delete user successful
     *
     * @return void
     */
    public function testDeleteUserSuccessful(): void
    {
        $this->client->request('POST', '/api/admin/user/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'user-id' => 3
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('User deleted successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
