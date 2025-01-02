<?php

namespace App\Tests\Auth;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LoginControllerTest
 *
 * Test cases for login API endpoint
 *
 * Note: this controller cannot be in the Controller namespace because it is fully managed by the JWT Auth bundle
 *
 * @package App\Tests\Auth
 */
class LoginControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test user login when request method is invalid
     *
     * @return void
     */
    public function testUserLoginWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/auth/login');

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test user login when email is blank
     *
     * @return void
     */
    public function testUserLoginWhenEmailIsBlank(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'email' => '',
            'password' => 'test'
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('The key "email" must be a non-empty string.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test user login when password is blank
     *
     * @return void
     */
    public function testUserLoginWhenPasswordIsBlank(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'email' => 'test@test.test',
            'password' => ''
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('The key "password" must be a non-empty string.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test user login when credentials are invalid
     *
     * @return void
     */
    public function testUserLoginWhenCredentialsAreInvalid(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'email' => 'fugazi@fufu.xyz',
            'password' => 'invalid-password'
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertSame('Invalid credentials.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test user login when credentials are valid
     *
     * @return void
     */
    public function testUserLoginWhenCredentialsAreValid(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'email' => 'test@test.test',
            'password' => 'test'
        ]) ?: null);

        /** @var array<string> $responseData */
        $responseData = $this->getResponseData($this->client->getResponse()->getContent());

        // assert response
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
