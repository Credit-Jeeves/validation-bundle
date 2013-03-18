<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QuestionsController extends Controller
{
    public function indexAction()
    {
        $sEmail = $this->container->getParameter('core.help.email');
        return $this->render('ComponentBundle:Questions:index.html.twig', array('sEmail' => $sEmail, 'sPhone' => ''));
    }
}
