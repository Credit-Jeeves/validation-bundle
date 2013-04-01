<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class User
{
    /**
     * 
     * @var string
     */
    const BAG_APPLICANT = 'applicant';

    /**
     * 
     * @var string
     */
    const BAG_DEALER = 'dealer';

    /**
     * 
     * @var string
     */
    const BAG_ADMIN = 'admin';

    protected $session;

    /**
     * @InjectParams({
     *     "session" = @Inject("Session")
     * })
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }
}