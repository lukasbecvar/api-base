<?php

namespace App\Controller;

use OpenApi\Attributes\Parameter;
use Throwable;
use OpenApi\Attributes\Tag;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ErrorController
 *
 * Controller for error handling
 *
 * @package App\Controller
 */
class ErrorController extends AbstractController
{
    /**
     * Handle error by code
     *
     * @param Request $request Request object
     *
     * @return JsonResponse Return error message as json response
     */
    #[Tag(name: "Error")]
    #[Parameter(name: 'code', in: 'query', description: 'Error code', required: true)]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The error message')]
    #[Route('/error', methods: ['GET'], name: 'error_by_code')]
    public function handleError(Request $request): JsonResponse
    {
        // get error code
        $code = $request->query->get('code', '400');

        // get error code as integer
        $code = intval($code);

        // error messages list
        $messages = [
            400 => 'bad request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'this route does not exist',
            405 => 'this request method is not allowed',
            426 => 'upgrade required',
            429 => 'too many requests',
            500 => 'internal server error',
            503 => 'service currently unavailable',
        ];

        // get error message
        $message = $messages[$code] ?? 'unknown error';

        // return error message as json response
        return $this->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    /**
     * Handle not found error
     *
     * @return JsonResponse Return not found error message as json response
     */
    #[Tag(name: "Error")]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'The not found error message')]
    #[Route('/error/notfound', methods:['GET'], name: 'error_not_found')]
    public function handleNotFoundError(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => 'this route does not exist!',
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Handle error exception
     *
     * @param Throwable $exception The exception object
     *
     * @return JsonResponse Return error message as json response
     */
    public function handleExceptionError(Throwable $exception): JsonResponse
    {
        // get exception code
        $statusCode = $exception instanceof HttpException
            ? $exception->getStatusCode() : JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        // build error response
        $response = [
            'status' => 'error',
            'message' => $exception->getMessage(),
        ];

        // encode response to json
        $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);

        // return json response
        return new JsonResponse($jsonResponse, $statusCode, [], true);
    }
}
