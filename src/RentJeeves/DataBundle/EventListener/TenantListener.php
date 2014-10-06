<?php

namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\PartnerCode;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\CoreBundle\Services\TUReporting;
use RentJeeves\DataBundle\Entity\Contract;
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

    protected $tuReporting;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function setTuReporting(TUReporting $tuReporting)
    {
        $this->tuReporting = $tuReporting;
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Tenant) {
            return;
        }

        $affiliateSource = $this->request ? $this->request->cookies->get('affiliateSource') : null;
//         $affiliateCode = $this->request ? $this->request->cookies->get('affiliateCode') : null;

        if (!$affiliateSource) {
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
//        $partnerCode->setCode($affiliateCode);
        $em->persist($partnerCode);

        $this->request->cookies->set('clearAffiliate', true);
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->turnOnTransUnionReporting($eventArgs);
    }

    public function turnOnTransUnionReporting(LifecycleEventArgs $eventArgs)
    {
        $tenant = $eventArgs->getEntity();
        if (!$tenant instanceof Tenant) {
            return;
        }

        if (!$eventArgs->hasChangedField('is_verified')) {
            return;
        }

        $contracts = $tenant->getContracts();
        $em = $eventArgs->getEntityManager();
        /**
         * @var Contract $contract
         */
        foreach ($contracts as $contract) {
            $this->tuReporting->turnOnTransUnionReporting($contract);
            $em->persist($contract);
        }
    }
}
