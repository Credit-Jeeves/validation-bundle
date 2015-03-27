<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class BaseController for shortcut methods
 */
class BaseController extends Controller
{
    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine')->getManager();
    }

    /**
     * @return \Symfony\Component\Translation\IdentityTranslator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }
}
