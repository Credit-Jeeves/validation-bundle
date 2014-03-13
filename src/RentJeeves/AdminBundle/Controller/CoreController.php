<?php

namespace RentJeeves\AdminBundle\Controller;

//use RentJeeves\CoreBundle\Report\TransUnion\TransUnionRentalReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionRentalReport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController as BaseController;

class CoreController extends BaseController
{
    /**
     * @Route("dashboard", name="sonata_admin_dashboard")
     * @Template()
     *
     * @return array
     */
    public function dashboardAction()
    {
        $request = $this->getRequest();
        $request->getSession()->set('contract_id', null);
        $request->getSession()->set('user_id', null);
        $request->getSession()->set('holding_id', null);
        $request->getSession()->set('landlord_id', null);
        $request->getSession()->set('group_id', null);
        $request->getSession()->set('property_id', null);
        return parent::dashboardAction();
    }

    /**
     * @Route("report", name="sonata_admin_report")
     * @Template()
     *
     * @return array
     */
    public function reportAction()
    {
        $startDate = new \DateTime('02-01-2014');
        $endDate = new \DateTime('03-01-2014');
        $report = new TransUnionRentalReport($this->container, $startDate, $endDate);
        $result = $this->get('jms_serializer')->serialize($report, 'tu_rental1');

        return new Response($result, 200);
    }
}
