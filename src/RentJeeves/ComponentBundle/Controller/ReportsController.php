<?php

namespace RentJeeves\ComponentBundle\Controller;

use RentJeeves\ComponentBundle\Form\BaseOrderReportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportsController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function baseOrderReportAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $group = $this->get('core.session.landlord')->getGroup();
        $form = $this->createForm(new BaseOrderReportType($user, $group));

        return array(
            'form' => $form->createView()
        );
    }
}
