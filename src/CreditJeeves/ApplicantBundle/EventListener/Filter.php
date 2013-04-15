<?php
namespace CreditJeeves\ApplicantBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter as FilterEvent;
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
     * @DI\Observe("applicant.filter")
     */
    public function checkReport(FilterEvent $event)
    {
        $sRouteName = $this->container->get('request')->get('_route');
        /** @var $user \CreditJeeves\DataBundle\Entity\User */
        $user = $this->container->get('core.session.applicant')->getUser();
        /** @var $route \Symfony\Bundle\FrameworkBundle\Routing\Router */
        $route = $this->container->get('router');
        // check new applicant
        if ($sRouteName == 'applicant_new') {
            return true;
        }
        // First check data
        if (!$user->getHasData()) {
            if ($sRouteName != 'applicant_returned') {
                return $event->getResponseEvent()->setResponse(
                    new RedirectResponse(
                        $route->generate('applicant_returned')
                    )
                );
            } else {
                return true;
            }
        }
        // Second - check if report exists
        if (!$user->getReportsPrequal()->last()) {
            return $event->getResponseEvent()->setResponse(new RedirectResponse($route->generate('core_report_get')));
        }
    }
}
