<?php

namespace App\Controller\Admin\User;

use App\Manager\UserManager;
use App\Manager\ErrorManager;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        summary: 'Delete user action (delete by user id for admin)',
        description: 'Delete user from database by user id and return status',
        tags: ['Admin (user management)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'user-id', type: 'int', description: 'User id', example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success user delete message'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_BAD_REQUEST,
                description: 'Invalid request data message'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_NOT_FOUND,
                description: 'User not found message'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                description: 'The delete error message'
            ),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/user/delete', methods:['POST'], name: 'admin_user_delete')]
    public function deleteUser(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'];

        // check if parameters are valid
        if (empty($userId)) {
            $this->errorManager->handleError(
                message: 'Parameter "status" are required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user id exist in database
        if (!$this->userManager->checkIfUserIdExistInDatabase($userId)) {
            $this->errorManager->handleError(
                message: 'User not found!',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete user
        $this->userManager->deleteUser($userId);

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'User deleted successfully!',
        ], JsonResponse::HTTP_OK);
    }
}
