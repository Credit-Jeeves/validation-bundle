<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TenantsListController extends Controller
{
    /**
     * @Template()
     * @return multitype:
     */
    public function indexAction($Group)
    {
        $contracts = $Group->getContracts();
        return array(
            'contracts' => $contracts,
        );
    }
}
