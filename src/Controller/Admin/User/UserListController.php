<?php

namespace App\Controller\Admin\User;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\UserManager;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserListController
 *
 * API controller for getting users list
 *
 * @package App\Controller\Admin\User
 */
class UserListController extends AbstractController
{
    private UserManager $userManager;
    private ErrorManager $errorManager;

    public function __construct(UserManager $userManager, ErrorManager $errorManager)
    {
        $this->userManager = $userManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Get users list (for admin users)
     *
     * @return JsonResponse The users list
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (user manager)")]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The users list',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(property: 'count', type: 'integer', example: 1),
                new Property(
                    property: 'users',
                    type: 'array',
                    items: new Items(
                        type: 'object',
                        properties: [
                            new Property(property: 'id', type: 'integer', example: 1),
                            new Property(property: 'email', type: 'string', example: 'test@test.test'),
                            new Property(property: 'first-name', type: 'string', example: 'test'),
                            new Property(property: 'last-name', type: 'string', example: 'User'),
                            new Property(
                                property: 'roles',
                                type: 'array',
                                items: new Items(type: 'string', example: 'ROLE_USER')
                            ),
                            new Property(property: 'register-time', type: 'string', format: 'date-time', example: '2024-11-17 07:31:27'),
                            new Property(property: 'last-login-time', type: 'string', format: 'date-time', example: '2025-01-31 10:50:29'),
                            new Property(property: 'ip-address', type: 'string', example: '172.19.0.1'),
                            new Property(property: 'browser', type: 'string', example: 'Chrome'),
                            new Property(property: 'status', type: 'string', example: 'active')
                        ]
                    )
                )
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_UNAUTHORIZED,
        description: 'The unauthorized message',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'error'),
                new Property(property: 'message', type: 'string', example: 'Unauthorized')
            ]
        )
    )]
    #[Route('/api/admin/user/list', methods:['GET'], name: 'admin_user_list')]
    public function getUsersList(): JsonResponse
    {
        // get users list
        try {
            $users = $this->userManager->getUsersList();
            return $this->json([
                'status' => 'success',
                'count' => count($users),
                'users' => $users,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'User list get failed',
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode()),
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
