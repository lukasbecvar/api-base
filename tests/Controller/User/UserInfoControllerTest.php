<?php

namespace App\Tests\User;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserInfoControllerTest
 *
 * Test cases for user info API endpoint
 *
 * @package App\Tests\User
 */
class UserInfoControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test user info when request method is invalid
     *
     * @return void
     */
    public function testUserInfoWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('POST', '/api/user/info');

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
     * Test user info when auth token is not provided
     *
     * @return void
     */
    public function testUserInfoWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/user/info');

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
     * Test user info when auth token is invalid
     *
     * @return void
     */
    public function testUserInfoWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/user/info', [], [], ['HTTP_AUTHORIZATION' => 'Bearer invalid-token']);

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
     * Test get user info with valid auth token
     *
     * @return void
     */
    public function testUserInfoGetSuccess(): void
    {
        $this->client->request('GET', '/api/user/info', [], [], [
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

        // assert response
        $this->assertNotEmpty($responseContent);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('first-name', $responseData);
        $this->assertArrayHasKey('last-name', $responseData);
        $this->assertArrayHasKey('roles', $responseData);
        $this->assertArrayHasKey('register-time', $responseData);
        $this->assertArrayHasKey('last-login-time', $responseData);
        $this->assertArrayHasKey('ip-address', $responseData);
        $this->assertArrayHasKey('user-agent', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
