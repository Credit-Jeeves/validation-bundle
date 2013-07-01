<?php
namespace CreditJeeves\DealerBundle\Controller;

use CreditJeeves\CoreBundle\Controller\DealerController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 */
class HomepageController extends Controller
{
    /**
     * @Route("/", name="dealer_homepage")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }
}
