<?php
namespace CreditJeeves\AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

class ApplicantAdminController extends Controller
{
    public function observeAction($id)
    {
        echo __METHOD__;
        exit;
        //process
        return array();
    }
}
