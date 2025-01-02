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
        tags: ['Admin (user management)'],
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
            ),
            new OA\Response(
                response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                description: 'The update error message'
            ),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/user/data/update/role', methods:['PATCH'], name: 'user_data_update_role')]
    public function updateUserRole(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'] ?? null;
        $task = $requestData['task'] ?? null;
        $role = $requestData['role'] ?? null;

        // check if request data is valid
        if ($userId === null || $task === null || $role === null || empty($userId) || empty($task) || empty($role)) {
            $this->errorManager->handleError(
                message: 'Parameters: user-id, task(add, remove), role are required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user id is valid
        if (!is_numeric($userId)) {
            $this->errorManager->handleError(
                message: 'User id is not valid!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if task is valid
        if (!in_array($task, ['add', 'remove'])) {
            $this->errorManager->handleError(
                message: 'Task is not valid!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($task === 'add') {
            // add role to user
            $this->userManager->addRoleToUser((int) $userId, $role);

            // return success message
            return $this->json([
                'status' => 'success',
                'message' => 'Role added successfully!'
            ], JsonResponse::HTTP_OK);
        } elseif ($task === 'remove') {
            // remove role from user
            $this->userManager->removeRoleFromUser((int) $userId, $role);

            // return success message
            return $this->json([
                'status' => 'success',
                'message' => 'Role removed successfully!'
            ], JsonResponse::HTTP_OK);
        } else {
            $this->errorManager->handleError(
                message: 'Task is not valid!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
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
        tags: ['Admin (user management)'],
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
    #[Route('/api/admin/user/data/update/status', methods: ['PATCH'], name: 'update_user_status')]
    public function updateUserStatus(Request $request): JsonResponse
    {
        // get request data
        $requestData = $request->toArray();
        $userId = $requestData['user-id'];
        $status = $requestData['status'];

        // check if parameters are valid
        if (empty($userId) || empty($status)) {
            $this->errorManager->handleError(
                message: 'Parameters user-id and status are required!',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user status already associated with user
        if ($this->userManager->getUserStatus($userId) === $status) {
            $this->errorManager->handleError(
                message: 'User status already set to: ' . $status,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // update user status
        $this->userManager->updateUserStatus($userId, $status);

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'User status updated successfully!',
        ], JsonResponse::HTTP_OK);
    }
}
