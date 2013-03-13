<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class ReportController extends Controller
{

    /**
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($name)
    {
        return $this->render('CoreBundle:Default:index.html.twig', array('name' => $name));
    }
}
