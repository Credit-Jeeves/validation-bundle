<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * This page would be usefull for development
 * @Route("/test")
 */
class TestController extends Controller
{
    /**
     * @Route("/", name="core_test")
     * @Template("CoreBundle::empty.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {

        $this->get('fp_badaboom.exception_catcher')->handleException(new \Exception('test'));
        return array();
    }

    /**
     * @Route("/error", name="core_test_error")
     * @Template()
     *
     * @return array
     */
    public function errorAction()
    {
        sdfgsdfgdsg();
        return array();
    }
}
