<?php
namespace CreditJeeves\ApplicantBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter as FilterEvent;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 * @DI\Service
 */
class Filter implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    protected function getUser()
    {
        if ($token = $this->container->get('security.context')->getToken()) {
            return $token->getUser();
        }
        return null;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRoute()
    {
        return $this->container->get('router');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @param string $text
     * @param array $arr
     *
     * @return string
     */
    protected function trans($text, $arr = array())
    {
        return $this->container->get('translator')->trans($text, $arr);
    }
    
    /**
     * @DI\Observe("applicant.filter")
     */
    public function isReturned(FilterEvent $event)
    {
        // First check data
        if (!$this->getUser()->getHasData()) {
            $event->stopPropagation();
            $type = $this->getUser()->getType();
            switch ($type) {
                case UserType::APPLICANT:
                    return $event->getResponseEvent()->setResponse(
                        new RedirectResponse(
                            $this->getRoute()->generate('applicant_returned')
                        )
                    );
                    break;
                case UserType::TETNANT:
                    return $event->getResponseEvent()->setResponse(
                        new RedirectResponse(
                            $this->getRoute()->generate('tenant_returned')
                        )
                    );
                    break;
            }
        }
    }

    /**
     * @DI\Observe("applicant.filter")
     */
    public function isVerified(FilterEvent $event)
    {
        if (UserIsVerified::FAILED == $this->getUser()->getIsVerified()) {
            $this->getSession()->getFlashBag()->add('message_title', $this->trans('pidkiq.title'));
            $this->getSession()->getFlashBag()->add(
                'message_body',
                $this->trans(
                    'pidkiq.error.lock-%SUPPORT_EMAIL%',
                    array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
                )
            );
            $event->stopPropagation();
            return $event->getResponseEvent()->setResponse(
                new RedirectResponse($this->getRoute()->generate('public_message_flash'))
            );
        } elseif (UserIsVerified::PASSED != $this->getUser()->getIsVerified()) {
            $event->stopPropagation();
            return $event->getResponseEvent()->setResponse(
                new RedirectResponse($this->getRoute()->generate('core_pidkiq'))
            );
        }
    }

    /**
     * @DI\Observe("applicant.filter")
     */
    public function checkReport(FilterEvent $event)
    {
        // Second - check if report exists
        if (!$this->getUser()->getReportsPrequal()->last()) {
            $event->stopPropagation();
            return $event->getResponseEvent()->setResponse(
                new RedirectResponse(
                    $this->getRoute()->generate('core_report_get', array('redirect' => 'applicant_homepage'))
                )
            );
        }
    }
}
