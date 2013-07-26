<?php
namespace CreditJeeves\CoreBundle\Session;

use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class User
{
    protected $data = array();

    protected $em;

    protected $session;

    /**
     * @InjectParams({
     *     "session" = @Inject("session"),
     *     "em" = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(Session $session, $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    public function isAdmin()
    {
        return $this->session->has(UserType::ADMIN);
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
