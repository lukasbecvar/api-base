<?php

namespace App\Controller\Admin\User;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\UserManager;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserDeleteController
 *
 * API controller for deleting user
 *
 * @package App\Controller\Admin\User
 */
class UserDeleteController extends AbstractController
{
    private UserManager $userManager;
    private ErrorManager $errorManager;

    public function __construct(UserManager $userManager, ErrorManager $errorManager)
    {
        $this->userManager = $userManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Delete user
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The status response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (user manager)")]
    #[Parameter(name: 'user_id', in: 'query', description: 'User id to delete', required: true)]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The user deleted successfully',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(property: 'message', type: 'string', example: 'User deleted successfully!')
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: 'User not found',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'error'),
                new Property(property: 'message', type: 'string', example: 'User not found!')
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: 'Parameter "status" are required!',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'error'),
                new Property(property: 'message', type: 'string', example: 'Parameter "user_id" are required!')
            ]
        )
    )]
    #[Route('/api/admin/user/delete', methods:['DELETE'], name: 'admin_user_delete')]
    public function deleteUser(Request $request): JsonResponse
    {
        $userId = (int) $request->request->get('user_id');

        // check if parameters are valid
        if (empty($userId)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Parameter "user_id" are required!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if user id exist in database
        if (!$this->userManager->checkIfUserIdExistInDatabase($userId)) {
            return $this->json([
                'status' => 'error',
                'message' => 'User id: ' . $userId . ' not found in database!'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // delete user
        try {
            $this->userManager->deleteUser($userId);
            return $this->json([
                'status' => 'success',
                'message' => 'User deleted successfully!',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'User delete error',
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode()),
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
