<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PaymentsListController extends Controller
{
    /**
     * @Template("RjComponentBundle:PaymentsList:landlord.html.twig")
     * @return multitype:
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Heartland');
        $total = $repo->countPayments($group);
        var_dump(count($total));
        return array(
        );
    }
}
