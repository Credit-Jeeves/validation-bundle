<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/")
 */
class EmailTemplateController extends Controller
{
    /**
     * @Route("/email_template/{nEmailId}/send_test", name="admin_email_template")
     * @Template()
     *
     * @return array
     */
    public function sendTestAction($nEmailId)
    {
        $User = $this->get('core.session.admin')->getUser();
        $email = $this->getDoctrine()->getRepository('RjEmailBundle:EmailTemplate')->find($nEmailId);
        $sTemplate = $email->getName();
        $sType = 'text/plain';
        if (preg_match('/(.+)\.(html|text)$/i', $sTemplate, $matches)) {
            if ($matches[2] == 'html') {
                $sType = 'text/html';
            }
        }
        $this->get('creditjeeves.mailer')->sendTestEmail($sTemplate, $sType);
        return new JsonResponse(array());
    }
}
