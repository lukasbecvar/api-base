<?php

namespace App\Tests\Controller\Admin\User;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserManagerControllerTest
 *
 * Test cases for user data update controller (API endpoint)
 *
 * @package App\Tests\Controller\Admin\User
 */
class UserManagerControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

   /**
     * Test update user role when request method is not valid
     *
     * @return void
     */
    public function testUpdateUserRoleWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/user/update/role');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update user role when auth token is not provided
     *
     * @return void
     */
    public function testUpdateUserRoleWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update user role when auth token is invalid
     *
     * @return void
     */
    public function testUpdateUserRoleWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role', [], [], [
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
     * Test update user role when user id is not valid
     *
     * @return void
     */
    public function testUpdateUserRoleWhenUserIdIsNotValid(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 'invalid-user-id',
            'task' => 'add',
            'role' => 'ROLE_ADMIN'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('User id is not valid!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user role when task is not valid
     *
     * @return void
     */
    public function testUpdateUserRoleWhenTaskIsNotValid(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 1,
            'task' => 'invalid-task',
            'role' => 'ROLE_ADMIN'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Task is not valid (allowed: add, remove)!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user role when role is not valid
     *
     * @return void
     */
    public function testUpdateUserRoleWhenRoleIsNotValid(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 1,
            'task' => 'add',
            'role' => null
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Parameters: user-id, task(add, remove), role are required!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user role when user role is already set
     *
     * @return void
     */
    public function testUpdateUserRoleWhenUserRoleIsAlreadySet(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 1,
            'task' => 'add',
            'role' => 'ROLE_ADMIN'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('User already has role: ROLE_ADMIN', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user role when successful
     *
     * @return void
     */
    public function testUpdateUserRoleWhenSuccessful(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/role', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 1,
            'task' => 'add',
            'role' => 'ROLE_TEST'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Role added successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test update user status when request method is not valid
     *
     * @return void
     */
    public function testUpdateUserStatusWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('POST', '/api/admin/user/update/status');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update user status when auth token is not provided
     *
     * @return void
     */
    public function testUpdateUserStatusWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/status');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update user status when auth token is invalid
     *
     * @return void
     */
    public function testUpdateUserStatusWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/status', [], [], [
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
     * Test update user status when user id is not provided
     *
     * @return void
     */
    public function testUpdateUserStatusWhenUserIdIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/status', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Request body is empty.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user status when request input data is empty
     *
     * @return void
     */
    public function testUpdateUserStatusWhenRequestInputDataIsEmpty(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/status', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => '',
            'status' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Parameters user-id and status are required!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user status when user status is already set
     *
     * @return void
     */
    public function testUpdateUserStatusWhenUserStatusIsAlreadySet(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/status', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 5,
            'status' => 'active'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('User status already set to: active.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user status successful
     *
     * @return void
     */
    public function testUpdateUserStatusSuccessful(): void
    {
        $this->client->request('PATCH', '/api/admin/user/update/status', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'user-id' => 5,
            'status' => 'inactive'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('User status updated successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
