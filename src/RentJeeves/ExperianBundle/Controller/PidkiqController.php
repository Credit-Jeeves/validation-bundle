<?php
namespace RentJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Controller\PidkiqController as Base;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @method User getUser()
 *
 * @Route("/")
 */
class PidkiqController extends Base
{
    /**
     * @Route("/check", name="core_pidkiq", options={"expose"=true})
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $result = parent::indexAction();
    }
}
