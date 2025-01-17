<?php

namespace App\Controller\Admin\User;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\UserManager;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Response;
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
    #[Response(response: JsonResponse::HTTP_OK, description: 'The users list')]
    #[Response(response: JsonResponse::HTTP_UNAUTHORIZED, description: 'The unauthorized message')]
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
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
