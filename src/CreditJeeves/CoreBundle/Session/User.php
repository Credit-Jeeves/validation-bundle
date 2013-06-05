<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class User
{
    /**
     * @TODO move to Enum
     * @var string
     */
    const USER_APPLICANT = 'applicant';

    /**
     * @TODO move to Enum
     * @var string
     */
    const USER_DEALER = 'dealer';

    /**
     *
     * @var string
     */
    const USER_ADMIN = 'admin';

    /**
     * 
     * @var string
     */
    const USER_TENANT = 'tenant';

    /**
     * 
     * @var string
     */
    const USER_LANDLORD = 'landlord';

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
        return $this->session->has(self::USER_ADMIN);
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
