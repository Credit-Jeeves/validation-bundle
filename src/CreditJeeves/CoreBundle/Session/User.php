<?php
namespace CreditJeeves\CoreBundle\Session;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;

abstract class User
{
    protected $data = array();

    /**
     * @var EntityManager
     */
    protected $em;

    protected $session;

    /**
     * @var SecurityContext
     */
    protected $security;

    /**
     * @InjectParams({
     *     "session" = @Inject("session"),
     *     "em" = @Inject("doctrine.orm.entity_manager"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct(Session $session, $em, $security)
    {
        $this->session = $session;
        $this->em = $em;
        $this->security = $security;
    }

    public function isAdmin()
    {
        return (bool) $this->session->get('observe_admin_id');
    }

    protected function findUser($nUserId)
    {
        return $this->em->getRepository('DataBundle:User')->find($nUserId);
    }

    protected function saveToSession($sNamespace)
    {
        $this->session->set($sNamespace, $this->serialize());
    }

    protected function getFromSession($sNamespace)
    {
        return $this->unserialize($this->session->get($sNamespace, ''));
    }

    protected function serialize()
    {
        return serialize($this->data);
    }

    protected function unserialize($serialized)
    {
        return unserialize($serialized);
    }
}
