<?php
namespace RentJeeves\TestBundle\Mailer;

use RentJeeves\CoreBundle\Mailer\Mailer as BaseMailer;

class Mailer extends BaseMailer
{
    /**
     * @param string $templateName
     * @param array  $params
     * @param string $emailTo
     * @param string $culture
     *
     * @return bool
     */
    public function sendBaseLetter($templateName, $params, $emailTo, $culture)
    {
        /** \Rj\EmailBundle\Entity\EmailTemplate $template */
        if (null == $template = $this->manager->findTemplateByName($templateName . '.html')) {
            $this->handleException(
                new \InvalidArgumentException(sprintf('Template with name "%s" not found', $templateName))
            );

            return false;
        }
        try {
            // $params is second for higher priority (for test email)
            $params = array_merge($this->defaultValuesForEmail, $params);
            if (null !== $user = $this->getUserByEmail($emailTo)) {
                if (false != $partner = $user->getPartner()) {
                    if (true === $partner->isPoweredBy()) {
                        $params['logoName'] = $partner->getLogoName();
                        $params['partnerName'] = $partner->getName();
                        $params['partnerAddress'] = $partner->getAddress();
                        $params['loginUrl'] = $partner->getLoginUrl();
                        $params['isPoweredBy'] = $partner->isPoweredBy();
                    }
                }
            }

            $htmlContent = $this->manager->renderEmail($template->getName(), $culture, $params);

            $message = \Swift_Message::newInstance();
            $message->setSubject($htmlContent['subject']);
            $message->setFrom([$htmlContent['fromEmail'] => $htmlContent['fromName']]);
            $message->setTo($emailTo);
            $message->addPart($htmlContent['body'], 'text/html');

            $this->container->get('mailer')->send($message);

            return true;
        } catch (\Twig_Error_Runtime $e) {
            $this->handleException($e);
        }

        return false;
    }
}
