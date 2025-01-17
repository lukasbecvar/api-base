<?php

namespace App\Controller\Admin\User;

use Exception;
use App\Manager\UserManager;
use App\Manager\ErrorManager;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserManagerController
 *
 * API controller for updating user data
 *
 * @package App\Controller\Admin\User
 */
class UserManagerController extends AbstractController
{
    private UserManager $userManager;
    private ErrorManager $errorManager;

    public function __construct(UserManager $userManager, ErrorManager $errorManager)
    {
        $this->userManager = $userManager;
        $this->errorManager = $errorManager;
    }

   /**
     * Update user role
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status response
     */
    #[OA\Patch(
        summary: 'User role update action (update by user id for admin)',
        description: 'Update user role and return status',
        tags: ['Admin (user manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'user-id', type: 'int', description: 'User id', example: 1),
                    new OA\Property(property: 'task', type: 'string', description: 'Task (add, remove)', example: 'add'),
                    new OA\Property(property: 'role', type: 'string', description: 'Role', example: 'ADMIN'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success user role update message'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_BAD_REQUEST,
                description: 'Invalid request data message'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_NOT_FOUND,
                description: 'User not found message'
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/user/update/role', methods:['PATCH'], name: 'user_data_update_role')]
    public function updateUserRole(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'] ?? null;
        $task = $requestData['task'] ?? null;
        $role = $requestData['role'] ?? null;

        // check if request data is valid
        if ($userId === null || $task === null || $role === null || empty($userId) || empty($task) || empty($role)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Parameters: user-id, task(add, remove), role are required!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if user id is valid
        if (!is_numeric($userId)) {
            return $this->json([
                'status' => 'error',
                'message' => 'User id is not valid!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if task is valid
        if (!in_array($task, ['add', 'remove'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Task is not valid (allowed: add, remove)!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // add role to user
        if ($task === 'add') {
            $this->userManager->addRoleToUser((int) $userId, $role);
            return $this->json([
                'status' => 'success',
                'message' => 'Role added successfully!'
            ], JsonResponse::HTTP_OK);
        // remove user role
        } elseif ($task === 'remove') {
            $this->userManager->removeRoleFromUser((int) $userId, $role);
            return $this->json([
                'status' => 'success',
                'message' => 'Role removed successfully!'
            ], JsonResponse::HTTP_OK);
        }

        $this->errorManager->handleError(
            message: 'Unexpected error to update user role!',
            code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Update user status
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status response
     */
    #[OA\Patch(
        summary: 'User status update action (update by user id for admin)',
        description: 'Update user status and return status',
        tags: ['Admin (user manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'user-id', type: 'int', description: 'User id', example: 1),
                    new OA\Property(property: 'status', type: 'string', description: 'Status', example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success user status update message'
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
                description: 'The update error message'
            ),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/user/update/status', methods: ['PATCH'], name: 'update_user_status')]
    public function updateUserStatus(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'];
        $status = $requestData['status'];

        // check if parameters are valid
        if (empty($userId) || empty($status)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Parameters user-id and status are required!'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if user status already associated with user
        if ($this->userManager->getUserStatus($userId) === $status) {
            return $this->json([
                'status' => 'error',
                'message' => 'User status already set to: ' . $status . '.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // update user status
        try {
            $this->userManager->updateUserStatus($userId, $status);
            return $this->json([
                'status' => 'success',
                'message' => 'User status updated successfully!',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'User status update error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
