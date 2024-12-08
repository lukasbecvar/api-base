<?php

namespace App\Middleware;

use Exception;
use App\Util\AppUtil;
use Psr\Log\LoggerInterface;
use App\Manager\ErrorManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class DatabaseOnlineMiddleware
 *
 * Middleware for checking the database connection
 *
 * @package App\Middleware
 */
class DatabaseOnlineMiddleware
{
    private AppUtil $appUtil;
    private Connection $connection;
    private LoggerInterface $logger;
    private ErrorManager $errorManager;

    public function __construct(
        AppUtil $appUtil,
        Connection $connection,
        LoggerInterface $logger,
        ErrorManager $errorManager
    ) {
        $this->logger = $logger;
        $this->appUtil = $appUtil;
        $this->connection = $connection;
        $this->errorManager = $errorManager;
    }

    /**
     * Check database connection status
     *
     * @param RequestEvent $event The request event
     *
     * @throws Exception Database connection error
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        try {
            // select for connection try
            $this->connection->executeQuery('SELECT 1');
        } catch (Exception $e) {
            // handle debug mode exception
            if ($this->appUtil->isDevMode()) {
                $this->errorManager->handleError(
                    message: 'database connection error: ' . $e->getMessage(),
                    code: Response::HTTP_INTERNAL_SERVER_ERROR
                );
            } else {
                $this->logger->error('database connection error: ' . $e->getMessage());
            }

            // return error response
            $response = new JsonResponse([
                'status' => 'error',
                'message' => 'database connection error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            $event->setResponse($response);
        }
    }
}
