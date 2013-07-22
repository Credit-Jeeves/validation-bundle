<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContractsListController extends Controller
{
    /**
     * @Template()
     * @return multitype:
     */
    public function indexAction()
    {
        $contracts = $this->get('core.session.applicant')->getUser()->getContracts();
        return array(
            'contracts' => $contracts,
        );
    }
}
