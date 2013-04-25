<?php
namespace CreditJeeves\ApplicantBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter as FilterEvent;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        return $this->container->get('core.session.applicant')->getUser();
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
            return $event->getResponseEvent()->setResponse(
                new RedirectResponse($this->getRoute()->generate('public_message_flash'))
            );
        } elseif (UserIsVerified::PASSED != $this->getUser()->getIsVerified()) {
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
        $sRouteName = $this->container->get('request')->get('_route');
        // First check data
        if (!$this->getUser()->getHasData()) {
            if ($sRouteName != 'applicant_returned') {
                return $event->getResponseEvent()->setResponse(
                    new RedirectResponse(
                        $this->getRoute()->generate('applicant_returned')
                    )
                );
            } else {
                return true;
            }
        }
        // Second - check if report exists
        if (!$this->getUser()->getReportsPrequal()->last()) {
            return $event->getResponseEvent()->setResponse(
                new RedirectResponse($this->getRoute()->generate('core_report_get'))
            );
        }
    }
}
