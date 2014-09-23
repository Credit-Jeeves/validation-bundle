<?php

namespace RentJeeves\OAuthServerBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface as SecurityContext;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

class ExceptionListener extends BaseExceptionListener
{
    protected function setTargetPath(Request $request)
    {
        if ($request->hasSession() && $request->isMethodSafe()) {
            $email = $request->get('email');
            $clientId = $request->get('client_id');
            if ($email) {
                $request->getSession()->set(SecurityContext::LAST_USERNAME, $email);
            }
            if ($clientId) {
                $request->getSession()->set('client_id', $clientId);
            }
        }
        parent::setTargetPath($request);
    }
}
