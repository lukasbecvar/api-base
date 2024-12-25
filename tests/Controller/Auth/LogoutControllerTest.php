<?php

namespace App\Tests\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class LogoutControllerTest
 *
 * Test cases for logout API endpoint
 *
 * @package App\Tests\Controller\Auth
 */
class LogoutControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test logout when request method is invalid
     *
     * @return void
     */
    public function testLogoutWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/auth/logout');

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
     * Test logout when token is blank
     *
     * @return void
     */
    public function testLogoutWhenTokenIsBlank(): void
    {
        $this->client->request('POST', '/api/auth/logout', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'token' => ''
        ]) ?: null);

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertEquals('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test logout when token is invalid
     *
     * @return void
     */
    public function testLogoutWhenTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/auth/logout', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'token' => 'invalid-token'
        ]) ?: null);

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertEquals('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test logout when token is valid
     *
     * @return void
     */
    public function testLogoutWhenTokenIsValid(): void
    {
        // make request to login endpoint (to get auth token)
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([
            'email' => 'test@test.test',
            'password' => 'test'
        ]) ?: null);

        // get login response
        $loginResponse = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$loginResponse) {
            $this->fail('Login response content is empty');
        }

        /** @var array<string> $loginResponseData */
        $loginResponseData = json_decode($loginResponse, true);

        // get auth token
        $authToken = $loginResponseData['token'];

        // make request to logout endpoint
        $this->client->request('POST', '/api/auth/logout', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $authToken
        ]);

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertNotEmpty($responseContent);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('user successfully logged out', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
