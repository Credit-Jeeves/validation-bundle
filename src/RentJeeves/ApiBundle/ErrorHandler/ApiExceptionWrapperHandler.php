<?php

namespace RentJeeves\ApiBundle\ErrorHandler;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @DI\Service("fos_rest.view.exception_wrapper_handler")
 */
class ApiExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{
    /**
     * @var Serializer
     * @DI\Inject("jms_serializer")
     */
    public $serializer;
    /**
     * @DI\Inject("translator")
     */
    public $translator;
    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return (new ExceptionWrapper($data, $this->translator))->getErrors();
    }

    /**
     * @param Exception $exception
     * @param Request $request
     * @return Response
     */
    public function handle(Exception $exception, Request $request)
    {
        $format = $request->attributes->has('_format') ? $request->attributes->get('_format') : 'json';

        $context = new SerializationContext();

        $context->setGroups([ErrorDescription::ERROR_GROUP]);

        $content = $this->serializer->serialize($this->wrap($exception), $format, $context);

        $response = new Response($content, Codes::HTTP_INTERNAL_SERVER_ERROR);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());

            $response->headers->replace($exception->getHeaders());
        }

        return $response;
    }
}
