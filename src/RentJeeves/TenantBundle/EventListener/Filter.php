<?php
namespace RentJeeves\TenantBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter as FilterEvent;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\ApplicantBundle\EventListener\Filter as BaseFilter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 * @DI\Service
 */
class Filter extends BaseFilter
{
    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    protected function getUser()
    {
        return $this->container->get('core.session.tenant')->getUser();
    }
    
    /**
     * @DI\Observe("tenant.filter")
     */
    public function isReturned(FilterEvent $event)
    {
        parent::isReturned($event);
    }
}
