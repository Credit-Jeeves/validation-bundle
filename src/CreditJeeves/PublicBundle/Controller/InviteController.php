<?php

namespace CreditJeeves\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/invite")
 *
 */
class InviteController extends Controller
{
    /**
     * @Route(
     *     "/{code}",
     *     name="applicant_invite"
     * )
     * @Template()
     * @param string $code
     * @return array
     */
    public function indexAction($code)
    {
        return array('code' => $code);
    }
}
