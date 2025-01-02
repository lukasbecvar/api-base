<?php

namespace App\Controller\Admin\User;

use OpenApi\Attributes\Tag;
use App\Manager\UserManager;
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

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Get users list (for admin users)
     *
     * @return JsonResponse The users list
     */
    #[Tag(name: "Admin (user management)")]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The users list')]
    #[Response(response: JsonResponse::HTTP_UNAUTHORIZED, description: 'The unauthorized message')]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'The error to get user list')]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/user/list', methods:['GET'], name: 'admin_user_list')]
    public function updateUserPassword(): JsonResponse
    {
        // get users list
        $users = $this->userManager->getUsersList();

        // return users list
        return $this->json([
            'status' => 'success',
            'users' => $users,
        ], JsonResponse::HTTP_OK);
    }
}
