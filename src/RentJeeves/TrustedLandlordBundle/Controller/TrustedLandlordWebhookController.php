<?php

namespace RentJeeves\TrustedLandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustedLandlordWebhookController extends BaseController
{
    /**
     * @Route("/trusted-landlord/jira-webhook/{jiraKey}", name="handle_jira_webhook")
     */
    public function handleJiraWebhookAction(Request $request, $jiraKey)
    {
        $data = $request->request->all();

        $result = $this->get('trusted_landlord.jira.service')->handleWebhookEvent($data);

        if ($result) {
            $httpCode = Response::HTTP_OK;
        } else {
            $httpCode = Response::HTTP_BAD_REQUEST;
        }

        return new Response('', $httpCode);
    }
}
