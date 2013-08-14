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
        return array(
        );
    }
}
