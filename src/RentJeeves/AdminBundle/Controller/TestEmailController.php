<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use RentJeeves\DataBundle\Entity\Tenant;
use Rj\EmailBundle\Entity\EmailTemplate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/")
 */
class TestEmailController extends BaseController
{
    const TEST_CULTURE = 'en';

    /**
     * @Route("/email_template/send_test/{id}", name="admin_email_send_test")
     * @ParamConverter("emailTemplate", class="RjEmailBundle:EmailTemplate")
     */
    public function sendTestAction(EmailTemplate $emailTemplate)
    {
        $enTranslation = $emailTemplate->getEnTranslation();
        $parameters = json_decode($enTranslation->getTestVariables(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->getSession()->getFlashBag()->add(
                'sonata_flash_error',
                $this->getTranslator()->trans('admin.email.actions.send_test.wrong_json')
            );
        } else {
            $user = new Tenant();
            $user->setEmailField($enTranslation->getTestEmailTo());
            $result = $this->getMailer()->sendBaseLetter(
                current(explode('.', $emailTemplate->getName())),
                $parameters ?: [],
                $user
            );
            if ($result === true) {
                $this->getSession()->getFlashBag()->add(
                    'sonata_flash_success',
                    $this->getTranslator()->trans('admin.email.actions.send_test.success')
                );
            } else {
                $this->getSession()->getFlashBag()->add(
                    'sonata_flash_error',
                    $this->getTranslator()->trans('admin.email.actions.send_test.failure')
                );
            }
        }

        return $this->redirectToRoute('email_template_edit', ['id' => $emailTemplate->getId()]);
    }

    /**
     * @return \CreditJeeves\CoreBundle\Mailer\Mailer
     */
    protected function getMailer()
    {
        return $this->get('project.mailer');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return \Symfony\Component\Translation\IdentityTranslator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }
}
