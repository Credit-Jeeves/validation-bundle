<?php
namespace CreditJeeves\ApplicantBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter as FilterEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
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
        /** @var $user \CreditJeeves\DataBundle\Entity\User */
        $user = $this->container->get('security.context')->getToken()->getUser();
        /** @var $route \Symfony\Bundle\FrameworkBundle\Routing\Router */
        $route = $this->container->get('router');

        if (!$user->getReportsPrequal()->last()) {
            return $event->getResponseEvent()->setResponse(new RedirectResponse($route->generate('core_report_get')));
        }
    }

    /**
     * @DI\Observe("applicant.filter")
     */
    public function checkStatus(FilterEvent $event)
    {
    }

    /**
     * @DI\Observe("applicant.filter")
     */
    public function checkData(FilterEvent $event)
    {
    }
}
