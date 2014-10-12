<?php

namespace RentJeeves\OAuthServerBundle\Security;

use RentJeeves\ApiBundle\ErrorHandler\ApiExceptionWrapperHandler;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("fos_oauth_server.security.entry_point")
 */
class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    protected $serverService;

    /**
     * @DI\InjectParams({
     *     "serverService" = @DI\Inject("fos_oauth_server.server"),
     * })
     */
    public function __construct(OAuth2 $serverService)
    {
        $this->serverService = $serverService;
    }

    /**
     * @todo This is only for Example how we can catch Exception need refactoring this part after discuss
     *
     * @param Request $request
     * @param AuthenticationException $authException
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $exception = new OAuth2AuthenticateException(
            OAuth2::HTTP_UNAUTHORIZED,
            OAuth2::TOKEN_TYPE_BEARER,
            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
            'access_denied',
            'OAuth2 authentication required'
        );

        return new Response(
            '',
            $exception->getHttpCode(),
            $exception->getResponseHeaders()
        );
    }
}
