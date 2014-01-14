<?php

namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\PartnerCode;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\HttpFoundation\Request;

/**
 * Configured in services.xml due to this error:
 * InactiveScopeException: You cannot create a service ("request") of an inactive scope ("request").
 * @see https://github.com/symfony/symfony/pull/7007
 */
class TenantListener
{
    protected $request;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Tenant) {
            return;
        }

        $affiliateSource = $this->request ? $this->request->cookies->get('affiliateSource') : null;
        $affiliateCode = $this->request ? $this->request->cookies->get('affiliateCode') : null;

        if (!$affiliateSource || !$affiliateCode) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $partner = $em->getRepository('DataBundle:Partner')->findOneByRequestName($affiliateSource);
        if (!$partner) {
            return;
        }

        $partnerCode = new PartnerCode();
        $partnerCode->setPartner($partner);
        $partnerCode->setUser($entity);
        $partnerCode->setCode($affiliateCode);
        $em->persist($partnerCode);

        $this->request->cookies->set('clearAffiliate', true);
    }
} 
