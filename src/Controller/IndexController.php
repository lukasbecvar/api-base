<?php

namespace App\Controller;

use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Response;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class IndexController
 *
 * Main index controller for check api status
 *
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * Index route for check api status
     *
     * @return JsonResponse Return backend status as json response
     */
    #[Tag(name: "Index")]
    #[Security(name: null)]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The api status')]
    #[Route('/', methods:['GET'], name: 'main_index')]
    public function index(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'api-base is running!',
            'version' => $_ENV['APP_VERSION'],
        ], JsonResponse::HTTP_OK);
    }
}
