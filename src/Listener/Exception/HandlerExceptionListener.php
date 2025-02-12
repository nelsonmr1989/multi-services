<?php

namespace App\Listener\Exception;

use App\Exception\Forbidden;
use App\Exception\NotFound;
use App\Exception\PaymentFailed;
use App\Exception\Validation as FormValidation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class HandlerExceptionListener
{
    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $details = null;
        $validation = null;
        $content = null;

        switch (true) {
            case $exception instanceof FormValidation:
                $httpCode = 400;
                $httpMessage = "Bad Request";
                $validation = $exception->getValidationMessages();

                if (isset($validation['messages'])) {
                    $validation = $validation['messages'];
                }
                break;
            case $exception instanceof PaymentFailed:
                $httpCode = 400;
                $httpMessage = "Payment Failed";
                $content = $exception->getMessage();
                $details = $exception->getErrorDetails();
                break;
            case $exception instanceof NotFound:
                $httpCode = 404;
                $content = $exception->getMessage();
                $httpMessage = "Not Found";
                break;
            case $exception instanceof Forbidden:
                $httpCode = 403;
                $content = $exception->getMessage();
                $httpMessage = "Forbidden";
                break;
            default:
                $httpCode = 500;
                $content = $exception->getMessage();
                $httpMessage = "Internal Server Error";
                break;
        }

        $response = new JsonResponse(['message' => $content], $httpCode, ["Content-Type", "application/json"]);

        if (!empty($details)) {
            $response = new JsonResponse($details, $httpCode, ["Content-Type", "application/json"]);
        }

        if (!empty($validation)) {
            $response = new JsonResponse($validation, $httpCode, ["Content-Type", "application/json"]);
        }



        $response->setStatusCode($httpCode, $httpMessage);

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
